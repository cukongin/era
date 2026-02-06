<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\AnggotaKelas;
use App\Models\NilaiSiswa;
use App\Models\TahunAjaran;
use App\Models\Periode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // 1. Filter Classes by Active Year
        $allClasses = Kelas::where('id_tahun_ajaran', $activeYear->id)->orderBy('nama_kelas')->get();
        $kelasId = $request->kelas_id;
        
        if (!$kelasId && $allClasses->count() > 0) {
            $kelasId = $allClasses->first()->id;
        }

        $selectedClass = Kelas::find($kelasId);
        $students = [];
        $metrics = ['total' => 0, 'promoted' => 0, 'retained' => 0];

        if ($selectedClass) {
            // Ensure Calculation is Run/Updated
            $this->calculate($selectedClass->id);

            $students = DB::table('promotion_decisions')
                ->join('siswa', 'promotion_decisions.id_siswa', '=', 'siswa.id')
                ->where('promotion_decisions.id_kelas', $selectedClass->id)
                ->where('promotion_decisions.id_tahun_ajaran', $activeYear->id)
                ->select('promotion_decisions.*', 'siswa.nama_lengkap', 'siswa.nis_lokal as nis')
                ->orderBy('siswa.nama_lengkap')
                ->get();

            $metrics['total'] = $students->count();
            $metrics['promoted'] = $students->where('system_recommendation', 'promoted')->count();
            $metrics['retained'] = $students->where('system_recommendation', 'retained')->count();
        }

        // Calculate Lock Status
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $latestYear = TahunAjaran::orderBy('id', 'desc')->first();
        $isLocked = false;
        
        if ($activeYear && $latestYear && $activeYear->id !== $latestYear->id) {
            if (!\App\Models\GlobalSetting::val('allow_edit_past_data', 0)) {
                $isLocked = true;
            }
        }

        // 5. Access Control (Final Period Check)
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
               ->orderBy('id', 'asc')
               ->get();
        
        $activePeriod = $periods->firstWhere('status', 'aktif');
        $lastPeriod = $periods->last();

        $isFinalPeriod = false;
        
        // Logical Check (ID or Name)
        if ($activePeriod && $lastPeriod && $activePeriod->id === $lastPeriod->id) {
             $isFinalPeriod = true;
        }

        // Additional Name Check (To be sure)
        if ($activePeriod) {
            $name = strtolower($activePeriod->nama_periode);
            if (str_contains($name, 'cawu 3') || str_contains($name, 'semester 2') || str_contains($name, 'genap')) {
                $isFinalPeriod = true;
            }
        }

        // Restriction Logic
        $user = Auth::user();
        $isAdmin = $user->role === 'admin' || $user->id === 1 || $user->isStaffTu(); 
        $warningMessage = null;

        // DEBUGGING ARSENAL
        $debugInfo = [
            'User' => $user->name . ' (' . $user->role . ') ID: ' . $user->id,
            'IsAdmin' => $isAdmin ? 'YES' : 'NO',
            'ActivePeriod' => $activePeriod ? $activePeriod->nama_periode . ' (ID: '.$activePeriod->id.')' : 'NONE',
            'LastPeriod' => $lastPeriod ? $lastPeriod->nama_periode . ' (ID: '.$lastPeriod->id.')' : 'NONE',
            'IsFinalPeriod' => $isFinalPeriod ? 'YES' : 'NO',
            'DetectionMethod' => 'Name Check + ID Match'
        ];

        if (!$isFinalPeriod) {
            if (!$isAdmin) {
                 // Force Block for Non-Admins
                 return redirect()->route('dashboard')->with('error', '⛔ AKSES DITOLAK: Halaman Kenaikan Kelas hanya aktif di Periode Akhir. (Debug: Period matches '.$debugInfo['ActivePeriod'].')');
            }
            // Admin Warning
            $warningMessage = "⚠️ PERINGATAN: Periode saat ini (" . ($activePeriod->nama_periode ?? '-') . ") BUKAN periode akhir.";
        }

        return view('promotion.index', compact('allClasses', 'selectedClass', 'students', 'metrics', 'isLocked', 'warningMessage', 'debugInfo'));
    }

    // THE LOGIC ENGINE
    public function calculate($kelasId)
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        $kelas = Kelas::find($kelasId);
        $jenjang = strtoupper($kelas->jenjang->kode); // MI / MTS
        
        // Get Settings
        $settings = DB::table('grading_settings')->where('jenjang', $jenjang)->first();
        if (!$settings) {
            $settings = (object) [
                'promotion_max_kkm_failure' => 3,
                'promotion_min_attendance' => 85,
                'promotion_min_attitude' => 'B',
                'kkm_default' => 70
            ];
        }

        $students = AnggotaKelas::where('id_kelas', $kelasId)->get();
        if ($students->isEmpty()) return;
        
        $studentIds = $students->pluck('id_siswa');
        
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('lingkup_jenjang', $jenjang)
            ->get();
        $periodIds = $periods->pluck('id');

        // EAGER LOADING / BULK FETCH
        // 1. Grades
        $allGrades = NilaiSiswa::whereIn('id_siswa', $studentIds)
            ->where('id_kelas', $kelasId)
            ->whereIn('id_periode', $periodIds)
            ->get()
            ->groupBy('id_siswa');

        // 2. KKM (Mapel specific)
        $allKkm = \App\Models\KkmMapel::where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang_target', $jenjang)
            ->get()
            ->keyBy('id_mapel'); 

        // 3. Attendance
        $allAttendance = DB::table('catatan_kehadiran')
            ->whereIn('id_siswa', $studentIds)
            ->whereIn('id_periode', $periodIds)
            ->get()
            ->groupBy('id_siswa');

        // 4. Weights (Bobot)
        $weights = \App\Models\BobotPenilaian::where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang', $jenjang)
            ->first();
        
        $weightConfig = [
            'harian' => $weights ? $weights->bobot_harian : 60,
            'uts' => $weights ? $weights->bobot_uts : 20,
            'uas' => $weights ? $weights->bobot_uas : 20
        ];

        $totalDays = \App\Models\GlobalSetting::val('total_effective_days', 220); 
        if ($totalDays <= 0) $totalDays = 220;


        foreach ($students as $student) {
            $report = []; 
            $fail = false;
            $sid = $student->id_siswa;

            // Get Student Grades from Collection
            $studentGrades = $allGrades[$sid] ?? collect([]);
            $mapelGrades = $studentGrades->groupBy('id_mapel');
            
            $kkmFailures = 0;
            $totalScore = 0;
            $mapelCount = 0;

            foreach ($mapelGrades as $mapelId => $vals) {
                // REVERTED: Use 'nilai_akhir' directly from DB. 
                // Assumes Admin has run "Hitung Ulang" to ensure data is fresh/pure.
                $avgMapel = $vals->avg('nilai_akhir');
                
                $totalScore += $avgMapel;
                $mapelCount++;

                // Lookup KKM
                $kkmVal = isset($allKkm[$mapelId]) ? $allKkm[$mapelId]->nilai_kkm : ($settings->kkm_default ?? 70);

                if ($avgMapel < $kkmVal) {
                    $kkmFailures++;
                }
            }

            $finalAvg = $mapelCount > 0 ? round($totalScore / $mapelCount, 2) : 0;
            $isConditional = false;

            // LOGIC CHECK KKM
            if ($kkmFailures > $settings->promotion_max_kkm_failure) {
                $fail = true;
                $report[] = "Gagal KKM pd $kkmFailures Mapel (> {$settings->promotion_max_kkm_failure})";
            } elseif ($kkmFailures == $settings->promotion_max_kkm_failure) {
                $isConditional = true;
                $report[] = "Perhatian: $kkmFailures Mapel < KKM (Batas Maksimal)";
            }

            // Attendance Logic
            $studentAttendance = $allAttendance[$sid] ?? collect([]);
            $totalAlpa = $studentAttendance->sum('tanpa_keterangan');
            $attendance = round((($totalDays - $totalAlpa) / $totalDays) * 100);

            if ($attendance < $settings->promotion_min_attendance) {
                $fail = true;
                $report[] = "Kehadiran {$attendance}% (< {$settings->promotion_min_attendance}%) - Alpa: $totalAlpa hari";
            } elseif ($attendance >= $settings->promotion_min_attendance && $attendance < ($settings->promotion_min_attendance + 5)) {
                $isConditional = true;
                $report[] = "Perhatian: Kehadiran {$attendance}% (Mepet KKM)";
            }

            // Attitude Logic
            $attitude = 'B';
            $hasKurang = false;
            foreach ($studentAttendance as $rec) {
                if ($rec->kelakuan === 'Kurang' || $rec->kerajinan === 'Kurang' || $rec->kebersihan === 'Kurang') {
                    $hasKurang = true;
                }
            }
            if ($hasKurang) $attitude = 'C';

            // Attitude Check
            $gradesOrder = ['A', 'B', 'C', 'D'];
            $minIdx = array_search($settings->promotion_min_attitude, $gradesOrder);
            $currIdx = array_search($attitude, $gradesOrder);
            if ($currIdx > $minIdx) {
                $fail = true;
                $report[] = "Sikap {$attitude} (Min {$settings->promotion_min_attitude})";
            }

            // DECISION
            if ($fail) {
                $recommendation = 'retained';
            } elseif ($isConditional) {
                // Conditional currently maps to Promoted usually, unless specific logic
                $recommendation = 'conditional';
            } else {
                $recommendation = 'promoted';
            }
            
            // Check Graduation (Is Final Year?)
            // Use 'tingkat_kelas' from DB if available, else fallback safely
            $gradeLevel = $kelas->tingkat_kelas ?? (int) filter_var($kelas->nama_kelas, FILTER_SANITIZE_NUMBER_INT);
            
            // Adjust Logic: MI (6), MTS (9 or 3 if using relative), MA (12)
            $isFinalYear = ($jenjang == 'MI' && $gradeLevel == 6) || 
                           ($jenjang == 'MTS' && ($gradeLevel == 9 || $gradeLevel == 3)) ||
                           ($jenjang == 'MA' && ($gradeLevel == 12 || $gradeLevel == 3));

            if ($isFinalYear) {
                if ($recommendation == 'promoted' || $recommendation == 'conditional') {
                    $recommendation = 'graduated';
                } elseif ($recommendation == 'retained') {
                    $recommendation = 'not_graduated';
                }
            }
            
            $reason = count($report) > 0 ? implode(', ', $report) : 'Memenuhi semua syarat.';

            // UPSERT DECISION
            DB::table('promotion_decisions')->updateOrInsert(
                [
                    'id_siswa' => $sid,
                    'id_kelas' => $kelasId,
                    'id_tahun_ajaran' => $activeYear->id
                ],
                [
                    'average_score' => $finalAvg,
                    'kkm_failure_count' => $kkmFailures,
                    'attendance_percent' => $attendance,
                    'attitude_grade' => $attitude,
                    'system_recommendation' => $recommendation,
                    'notes' => $reason,
                    'updated_at' => now()
                 ]
            );
            
            // Auto-Sync if NOT Overridden manually
            $existing = DB::table('promotion_decisions')
                 ->where('id_siswa', $sid)
                 ->where('id_kelas', $kelasId)
                 ->where('id_tahun_ajaran', $activeYear->id)
                 ->first();
                 
            if ($existing && is_null($existing->override_by)) {
                 DB::table('promotion_decisions')
                     ->where('id', $existing->id)
                     ->update(['final_decision' => $recommendation]);
            }
        }
    }

    public function updateDecision(Request $request) 
    {
        if (!$this->checkActiveYear()) {
             return response()->json(['message' => '⚠️ AKSES DITOLAK: Periode terkunci.'], 403);
        }

        DB::table('promotion_decisions')
            ->where('id', $request->decision_id)
            ->update([
                'final_decision' => $request->status,
                'override_by' => Auth::id(),
                'updated_at' => now()
            ]);
            
        return response()->json(['message' => 'Status saved']);
    }

    public function processPromotion(Request $request)
    {
        if (!$this->checkActiveYear()) {
             return back()->with('error', '⚠️ AKSES DITOLAK: Periode terkunci.');
        }

        return back()->with('success', 'Data kenaikan kelas berhasil diproses. Siswa akan dipindahkan pada Tutup Tahun Buku.');
    }

    public function processAll(Request $request)
    {
        if (!$this->checkActiveYear()) {
             return back()->with('error', '⚠️ AKSES DITOLAK: Periode terkunci.');
        }

        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        $allClasses = Kelas::where('id_tahun_ajaran', $activeYear->id)->get();
        
        $count = 0;
        foreach ($allClasses as $kelas) {
            $this->calculate($kelas->id);
            $count++;
        }

        return back()->with('success', "Berhasil menghitung ulang status kenaikan untuk $count kelas.");
    }

    public function finalize(Request $request)
    {
        if (!$this->checkActiveYear()) {
             return back()->with('error', '⚠️ AKSES DITOLAK: Periode terkunci.');
        }

        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // Lock all decisions by setting override_by (so they are treated as manual decisions)
        // Only lock those that are not yet locked/overridden
        $affected = DB::table('promotion_decisions')
            ->where('id_tahun_ajaran', $activeYear->id)
            ->whereNull('override_by')
            ->update([
                'override_by' => Auth::id(),
                'updated_at' => now()
            ]);

        return back()->with('success', "Status Kenaikan Kelas BERHASIL DIKUNCI PERMANEN. ($affected Data Di-finalisasi). Tombol 'Hitung Semua' tidak akan mengubah data ini lagi.");
    }

    private function checkActiveYear() 
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) return true; // Setup mode
        
        // 1. Check Global Switch
        $allowEdit = \App\Models\GlobalSetting::val('allow_edit_past_data', 0);
        if ($allowEdit) return true;

        // 2. Check if Current Year is Latest
        $latestYear = TahunAjaran::orderBy('id', 'desc')->first();
        if ($latestYear && $activeYear->id === $latestYear->id) {
            return true; // Latest year is always editable
        }

        // If Old Year & Lock is ON -> Block
        return false;
    }
}
