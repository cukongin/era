<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use App\Models\NilaiSiswa;
use App\Models\Periode;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->isWaliKelas()) {
            return redirect()->route('walikelas.dashboard');
        }

        if (auth()->user()->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        }

        if (auth()->user()->isStaffTu()) {
            return redirect()->route('tu.dashboard');
        }

        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) {
            return view('dashboard.error', ['message' => 'Tidak ada Tahun Ajaran Aktif']);
        }

        // --- 1. General Stats ---
        $countSiswa = Siswa::where('status_siswa', 'aktif')->count();
        $countGuru = User::where('role', 'guru')->count();
        $countKelas = Kelas::where('id_tahun_ajaran', $activeYear->id)->count();

        // Split MI vs MTs Students (Based on Class Jenjang)
        // Assumption: Active students are in a class for the active year.
        $siswaMi = Siswa::where('status_siswa', 'aktif')
            ->whereHas('anggota_kelas.kelas', function($q) use ($activeYear) {
                $q->where('id_tahun_ajaran', $activeYear->id)
                  ->whereHas('jenjang', fn($sq) => $sq->where('kode', 'MI'));
            })->count();
            
        $siswaMts = Siswa::where('status_siswa', 'aktif')
            ->whereHas('anggota_kelas.kelas', function($q) use ($activeYear) {
                $q->where('id_tahun_ajaran', $activeYear->id)
                  ->whereHas('jenjang', fn($sq) => $sq->where('kode', 'MTs')->orWhere('kode', 'MTS'));
            })->count();

        // If counts are zero (maybe seeding issue or no class assignment), just roughly split or show 0
        if ($countSiswa > 0 && $siswaMi == 0 && $siswaMts == 0) {
            // Fallback logic if needed, or just leave as is
        }

        $stats = [
            'total_siswa' => $countSiswa,
            'siswa_mi' => $siswaMi,
            'siswa_mts' => $siswaMts,
            'total_guru' => $countGuru,
            'total_kelas' => $countKelas
        ];


        // --- 2. MI Stats (Diniyah / Cawu) ---
        // Find active Cawu period for MI
        $miPeriod = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('lingkup_jenjang', 'MI')
            ->where('status', 'aktif')
            ->first();

        // Count MI classes
        $totalMiClasses = Kelas::where('id_tahun_ajaran', $activeYear->id)
            ->whereHas('jenjang', fn($q) => $q->where('kode', 'MI'))
            ->count();

        $miStats = [
            'active_cawu' => $miPeriod ? (int) filter_var($miPeriod->nama_periode, FILTER_SANITIZE_NUMBER_INT) : 1, // Extract number from "Cawu 1"
            'finalized_classes' => 0, // Todo: Count classes that finalized grades
            'total_classes' => $totalMiClasses,
            'days_left' => $miPeriod && $miPeriod->end_date ? \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($miPeriod->end_date), false) : null
        ];


        // --- 3. MTs Stats (Semester) ---
        $mtsPeriod = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where(function($q) {
                $q->where('lingkup_jenjang', 'MTs')->orWhere('lingkup_jenjang', 'MTS');
            })
            ->where('status', 'aktif')
            ->first();
            
        $mtsStats = [
            'active_semester' => $mtsPeriod ? (str_contains(strtolower($mtsPeriod->nama_periode), 'ganjil') ? 1 : 2) : 1,
            'status' => 'Sedang Berjalan',
            'deadline' => $mtsPeriod ? $mtsPeriod->end_date : null,
            'waiting_classes' => 0 // Todo
        ];


        // --- 4. Ongoing Classes (Monitoring) ---
        // Fetch top 5 classes needing attention (e.g. deadline approaching)
        $allClasses = Kelas::where('id_tahun_ajaran', $activeYear->id)
            ->with(['wali_kelas', 'jenjang'])
            ->get();

        $ongoingClasses = [];
        foreach ($allClasses as $kelas) {
            // Determine relevant period for this class
            $p = ($kelas->jenjang->kode == 'MI') ? $miPeriod : $mtsPeriod;
            
            $deadlineDiff = null;
            if ($p && $p->end_date) {
                $deadlineDiff = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($p->end_date), false);
            }

            $ongoingClasses[] = [
                'id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
                'jenjang' => $kelas->jenjang->kode,
                'wali_kelas' => $kelas->wali_kelas->name ?? 'Belum Ada',
                'wali_id' => $kelas->id_wali_kelas,
                'status' => 'Dalam Proses', // Legacy, kept for safety
                'deadline_diff' => $deadlineDiff ? (int)$deadlineDiff : null,
                'mapel_id' => null, 
                'is_teacher' => false,
                'am_i_wali' => false,
                
                // NEW: Grading Progress
                'total_mapel' => \App\Models\PengajarMapel::where('id_kelas', $kelas->id)->count(),
                'graded_mapel' => 0, // Calculated below
                'status_nilai' => 'Belum Mulai' // Default
            ];
            
            // Calculate Graded Mapel
            $currentIndex = count($ongoingClasses) - 1;
            if ($p) {
                $studentIds = \App\Models\AnggotaKelas::where('id_kelas', $kelas->id)->pluck('id_siswa');
                $gradedCount = \App\Models\NilaiSiswa::whereIn('id_siswa', $studentIds)
                    ->where('id_periode', $p->id)
                    ->distinct('id_mapel')
                    ->count('id_mapel');
                
                $ongoingClasses[$currentIndex]['graded_mapel'] = $gradedCount;
                
                if ($ongoingClasses[$currentIndex]['total_mapel'] > 0) {
                     if ($gradedCount >= $ongoingClasses[$currentIndex]['total_mapel']) {
                         $ongoingClasses[$currentIndex]['status_nilai'] = 'Selesai';
                     } elseif ($gradedCount > 0) {
                         $ongoingClasses[$currentIndex]['status_nilai'] = 'Proses';
                     }
                }
            }
        }

        return view('dashboard', compact('stats', 'activeYear', 'miStats', 'mtsStats', 'ongoingClasses'));
    }

    public function remindWali(Request $request)
    {
        $waliId = $request->wali_id;
        $kelasName = $request->kelas_name;
        $customMessage = $request->message;
        $type = $request->type ?? 'warning';
        
        if (!$waliId) return back()->with('error', 'Kelas ini tidak memiliki Wali Kelas.');

        $prefix = ($type == 'warning') ? "PENGINGAT" : "INFO";
        
        $messageBody = $customMessage 
            ? "$prefix: $customMessage"
            : "PENGINGAT: Progres penilaian kelas $kelasName belum lengkap. Mohon segera dicek.";

        \App\Models\Notification::create([
            'user_id' => $waliId,
            'message' => $messageBody,
            'type' => $type
        ]);

        return back()->with('success', 'Pesan berhasil dikirim ke Wali Kelas.');
    }

    public function remindBulk(Request $request)
    {
        $classIds = $request->class_ids;
        $customMessage = $request->message;
        $type = $request->type ?? 'warning';
        
        if (empty($classIds)) return back()->with('error', 'Tidak ada kelas yang dipilih.');

        $count = 0;
        $prefix = ($type == 'warning') ? "PENGINGAT" : "INFO";

        foreach ($classIds as $kelasId) {
            $kelas = Kelas::find($kelasId);
            if ($kelas && $kelas->id_wali_kelas) {
                $messageBody = $customMessage 
                    ? "$prefix: $customMessage" 
                    : "PENGINGAT: Progres penilaian kelas {$kelas->nama_kelas} belum lengkap.";
                
                \App\Models\Notification::create([
                    'user_id' => $kelas->id_wali_kelas,
                    'message' => $messageBody,
                    'type' => $type
                ]);
                $count++;
            }
        }

        return back()->with('success', "$count Peringatan masal berhasil dikirim.");
    }

    public function markNotificationRead($id)
    {
        $notif = \App\Models\Notification::where('user_id', auth()->id())->where('id', $id)->firstOrFail();
        $notif->update(['is_read' => true]);
        return back();
    }
}
