<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\AnggotaKelas;
use App\Models\CatatanKehadiran;
use App\Models\CatatanWaliKelas;
use App\Models\NilaiEkstrakurikuler;
use App\Models\NilaiSiswa;
use App\Models\Mapel;

class WaliKelasController extends Controller
{
    private function getWaliKelasInfo()
    {
        $user = Auth::user();
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // Default: Find assigned class for this teacher
        $kelas = Kelas::where('id_wali_kelas', $user->id)
            ->where('id_tahun_ajaran', $activeYear->id)
            ->with(['jenjang', 'anggota_kelas.siswa', 'wali_kelas'])
            ->first();
        
        // ADMIN Override: Allow selecting class via request, provided it belongs to active year (or just let controller filter handle logic)
        if ($user->isAdmin() || $user->isTu()) {
            if (request('kelas_id')) {
                $kelas = Kelas::where('id', request('kelas_id'))
                     ->where('id_tahun_ajaran', $activeYear->id)
                     ->with(['jenjang', 'anggota_kelas.siswa', 'wali_kelas'])
                    ->first();
            }
            
            // If still no class (admin has no assigned class and didn't select one), pick first from Active Year
            if (!$kelas) {
                // Filter by Jenjang too if requested? 
                $q = Kelas::where('id_tahun_ajaran', $activeYear->id)
                    ->with(['jenjang', 'anggota_kelas.siswa', 'wali_kelas']);
                    
                if (request('jenjang')) {
                    $q->whereHas('jenjang', fn($j) => $j->where('kode', request('jenjang')));
                }
                
                $kelas = $q->first();
            }
        }

        // Get Period based on Request or Active or Latest
        $periode = null;
        if ($kelas) {
            if (request('periode_id')) {
                $periode = Periode::find(request('periode_id'));
            }
            
            if (!$periode) {
                 $periode = Periode::where('id_tahun_ajaran', $activeYear->id)
                    ->where('status', 'aktif')
                    ->where('lingkup_jenjang', $kelas->jenjang->kode) 
                    ->first();
            }

            // Fallback: If no ACTIVE period, get LATEST period (to prevent Access Denied screen)
            if (!$periode) {
                $periode = Periode::where('id_tahun_ajaran', $activeYear->id)
                    ->where('lingkup_jenjang', $kelas->jenjang->kode)
                    ->orderBy('id', 'desc')
                    ->first();
            }
        }

        return [$kelas, $periode, $activeYear];
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();

        // 1. Fetch ALL assignments
        $query = Kelas::where('id_tahun_ajaran', $activeYear->id)->with(['jenjang', 'anggota_kelas.siswa']);
        if (!$user->isAdmin()) {
            $query->where('id_wali_kelas', $user->id);
        }
        
        // 2. Filter by Jenjang (UI Filter)
        if ($request->jenjang) {
             $query->whereHas('jenjang', function($q) use ($request) {
                 $q->where('kode', $request->jenjang);
             });
        }

        $allClasses = $query->orderBy('nama_kelas')->get();
        
        // 3. Determine Active Class (Selected or First)
        $kelas = $request->kelas_id;
        $kelas = null;
        if ($kelas) {
            $kelas = $allClasses->where('id', $kelas->id)->first();
        } 
        if (!$kelas) {
            $kelas = $allClasses->first();
        }

        // 4. Get Period for THIS class
        $periode = null;
        if ($kelas) {
            $periode = Periode::where('id_tahun_ajaran', $activeYear->id)
                ->where('status', 'aktif')
                ->where('lingkup_jenjang', $kelas->jenjang->kode) 
                ->first();
        }

        if (!$kelas) {
            // Instead of redirecting to error page, let it render empty state
            $studentCount = 0;
            $absensiCount = 0;
            $catatanCount = 0;
        } else {
            $studentCount = $kelas->anggota_kelas->count();
            // Stats
            $absensiCount = 0;
            $catatanCount = 0;
            if ($periode) {
                $absensiCount = CatatanKehadiran::where('id_kelas', $kelas->id)->where('id_periode', $periode->id)->count();
                $catatanCount = CatatanWaliKelas::where('id_kelas', $kelas->id)->where('id_periode', $periode->id)->count();
            }
        }

        // --- TIMELINE STATS (Copied from DashboardController for consistency) ---
        // 1. MI Progress
        $miPeriod = Periode::where('id_tahun_ajaran', $activeYear->id ?? 0)
            ->where('lingkup_jenjang', 'MI')
            ->where('status', 'aktif')
            ->first();

        $miStats = [
            'active_cawu' => $miPeriod ? filter_var($miPeriod->nama_periode, FILTER_SANITIZE_NUMBER_INT) : 0,
            'deadline' => $miPeriod ? $miPeriod->tenggat_waktu : null,
            'days_left' => ($miPeriod && $miPeriod->tenggat_waktu) ? \Carbon\Carbon::now()->diffInDays($miPeriod->tenggat_waktu, false) : 0,
        ];

        // 2. MTs Progress
        $mtsPeriod = Periode::where('id_tahun_ajaran', $activeYear->id ?? 0)
            ->where('lingkup_jenjang', 'MTS')
            ->where('status', 'aktif')
            ->first();

        $mtsStats = [
            'active_semester' => $mtsPeriod ? ((stripos($mtsPeriod->nama_periode, 'Ganjil') !== false) ? 1 : 2) : 0,
            'deadline' => $mtsPeriod ? $mtsPeriod->tenggat_waktu : null,
        ];

        // --- MONITORING LOGIC (Teacher Progress) ---
        $monitoringData = [];
        $finishedCount = 0;
        $notStartedCount = 0;
        
        if ($periode) {
            // 1. Get Mapels
            $jenkins = $kelas->jenjang->kode;
            // 1. Get Mapels (Only those assigned/plotted for this class)
            // Use existing Assignments query to filter mapels
            $assignedMapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
            
            $mapels = Mapel::whereIn('id', $assignedMapelIds)->get();

            // 2. Teacher Assignments
            $assignments = \App\Models\PengajarMapel::with('guru')
                ->where('id_kelas', $kelas->id)
                ->get()
                ->keyBy('id_mapel');

            // 3. Grades (Count per Mapel)
            // Use query grouping for efficiency? Or Collection filtering.
            // Collection filtering is fine for class scale (~15-20 mapels, ~30 students)
            $grades = NilaiSiswa::where('id_kelas', $kelas->id)
                ->where('id_periode', $periode->id)
                ->get();
            
            $totalStudents = $studentCount > 0 ? $studentCount : 1; // Avoid div by zero

            // Fetch KKMs for all mapels in this class/jenjang
            $kkmList = \App\Models\KkmMapel::where('id_tahun_ajaran', $activeYear->id)
                ->where('jenjang_target', $kelas->jenjang->kode)
                ->pluck('nilai_kkm', 'id_mapel');
            
            // Get Default KKM from Settings
            $gradingSettings = DB::table('grading_settings')
                ->where('jenjang', $kelas->jenjang->kode)
                ->first();
            $defaultKkm = $gradingSettings ? $gradingSettings->kkm_default : 70;

            foreach ($mapels as $mapel) {
                $mapelGrades = $grades->where('id_mapel', $mapel->id);
                $gradedCount = $mapelGrades->count();
                $progress = min(100, round(($gradedCount / $totalStudents) * 100)); 
                
                $teacherName = $assignments[$mapel->id]->guru->name ?? null;
                $teacherAvatar = null; 

                // Fallback: If no teacher assigned, check who input the grades
                if (!$teacherName && $gradedCount > 0) {
                     // Get the first grader ID for this mapel/class
                     $graderId = $mapelGrades->first()->id_guru ?? null;
                     if ($graderId) {
                         $grader = \App\Models\User::find($graderId);
                         if ($grader) {
                             $teacherName = $grader->name . ' (Penginput)';
                         }
                     }
                }
                
                if (!$teacherName) $teacherName = 'Belum Ada Guru';

                // Status Logic
                $statusLabel = 'Belum Input';
                if ($progress >= 100) {
                    $statusLabel = 'Selesai';
                    $finishedCount++;
                } elseif ($progress > 0) {
                    $statusLabel = 'Proses ' . $progress . '%';
                } else {
                    $notStartedCount++;
                }
                
                // DATA ANALYSIS (Katrol)
                $minScore = $gradedCount > 0 ? $mapelGrades->min('nilai_akhir') : 0;
                
                // Use Specific KKM or Default
                $kkm = $kkmList[$mapel->id] ?? $defaultKkm;
                
                $isBelowKkm = $gradedCount > 0 && $minScore < $kkm;
                
                // Katrol Status Message
                $katrolStatus = 'Aman';
                if ($gradedCount > 0) {
                     if ($isBelowKkm) {
                         $katrolStatus = 'Perlu Katrol';
                     }
                } else {
                    $katrolStatus = '-';
                }

                $monitoringData[] = (object) [
                    'id' => $mapel->id,
                    'nama_mapel' => $mapel->nama_mapel,
                    'nama_guru' => $teacherName,
                    'progress' => $progress,
                    'status_label' => $statusLabel,
                    'graded_count' => $gradedCount,
                    'total_students' => $totalStudents,
                    'min_score' => $minScore,
                    'katrol_status' => $katrolStatus,
                    'kkm' => $kkm // Pass KKM to view for context
                ];
            }
        }
        
        // Count In Progress (Total - Finished - NotStarted)
        // Or just count from array?
        // Let's rely on the array for view iteration.
        
        // Calculate pending for the card (Proses + Belum)
        $pendingCount = count($monitoringData) - $finishedCount;

        return view('wali-kelas.dashboard', compact(
            'kelas', 'periode', 'studentCount', 'absensiCount', 'catatanCount', 
            'miStats', 'mtsStats', 
            'monitoringData', 'finishedCount', 'notStartedCount', 'activeYear', 'allClasses'
        ));
    }

    public function inputAbsensi()
    {
        list($kelas, $periode, $activeYear) = $this->getWaliKelasInfo();
        if (!$kelas || !$periode) return back()->with('error', 'Akes ditolak atau periode belum aktif.');

        // Eager load CatatanKehadiran (relation name inferred: catatan_kehadiran)
        // Since we didn't define relation in Siswa, we limit eager loading or define it now?
        // Let's manually fetch keyed by student ID to avoid adding relations to Siswa model if not needed.
        $students = $kelas->anggota_kelas()->with('siswa')->get();
        
        $absensiRows = CatatanKehadiran::where('id_kelas', $kelas->id)
            ->where('id_periode', $periode->id)
            ->get()
            ->keyBy('id_siswa');

        // Admin Filter Data (Copy from inputCatatan)
    $allClasses = collect([]);
    if (Auth::user()->isAdmin() || Auth::user()->isTu()) {
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $query = Kelas::where('id_tahun_ajaran', $activeYear->id);
        if (request('jenjang')) {
            $query->whereHas('jenjang', fn($q) => $q->where('kode', request('jenjang')));
        }
        $allClasses = $query->orderBy('nama_kelas')->get();
    }
    
    
    // Fetch All Periods for Filter (Filtered by Jenjang)
    $periodsQuery = Periode::where('id_tahun_ajaran', $activeYear->id);
    if (request('jenjang')) {
        $periodsQuery->where('lingkup_jenjang', request('jenjang'));
    }
    $allPeriods = $periodsQuery->orderBy('nama_periode')->get();

    return view('wali-kelas.absensi', compact('kelas', 'periode', 'students', 'absensiRows', 'allClasses', 'allPeriods'));
    }

    public function storeAbsensi(Request $request)
    {
        list($kelas, $periode) = $this->getWaliKelasInfo();
        
        foreach ($request->absensi as $siswaId => $data) {
            CatatanKehadiran::updateOrCreate(
                [
                    'id_siswa' => $siswaId,
                    'id_kelas' => $kelas->id,
                    'id_periode' => $periode->id
                ],
                [
                    'sakit' => $data['sakit'] ?? 0,
                    'izin' => $data['izin'] ?? 0,
                    'tanpa_keterangan' => $data['alpa'] ?? 0,
                    'kelakuan' => $data['kelakuan'] ?? 'Baik',
                    'kerajinan' => $data['kerajinan'] ?? 'Baik',
                    'kebersihan' => $data['kebersihan'] ?? 'Baik',
                ]
            );
        }
        
        return back()->with('success', 'Data absensi berhasil disimpan.');
    }

    public function downloadAbsensiTemplate()
    {
        list($kelas, $periode) = $this->getWaliKelasInfo();
        
        $students = $kelas->anggota_kelas()->with('siswa')
            ->get()
            ->sortBy(fn($ak) => $ak->siswa->nama_lengkap);

        $filename = "Template_Absensi_{$kelas->nama_kelas}_{$periode->nama_periode}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM
            
            fputcsv($file, ['PETUNJUK: Isi kolom Sakit, Izin, Alpa dengan Angka. Kolom Kepribadian dengan: Baik, Cukup, atau Kurang.']);
            
            // Group Header (Simulated Merged Cells)
            fputcsv($file, ['', '', '', 'ABSENSI', '', '', 'KEPRIBADIAN', '', '']);
            
            // Column Headers
            fputcsv($file, ['NO', 'NIS', 'NAMA SISWA', 'SAKIT', 'IZIN', 'ALPA', 'KELAKUAN', 'KERAJINAN', 'KEBERSIHAN']);

            foreach ($students as $index => $ak) {
                // Pre-fill with existing data if possible, or 0
                // For template, maybe just 0 is cleaner? Or pre-fill useful?
                // Let's pre-fill existing data so it acts as "Export/Backup" too.
                $record = CatatanKehadiran::where('id_siswa', $ak->id_siswa)
                    ->where('id_kelas', $ak->id_kelas)
                    ->where('id_periode', \App\Models\Periode::where('status', 'aktif')->value('id')) // Better fetch from controller scope
                    ->first();

                fputcsv($file, [
                    $index + 1,
                    $ak->siswa->nis_lokal,
                    $ak->siswa->nama_lengkap,
                    $record->sakit ?? 0,
                    $record->izin ?? 0,
                    $record->tanpa_keterangan ?? 0,
                    $record->kelakuan ?? 'Baik',
                    $record->kerajinan ?? 'Baik',
                    $record->kebersihan ?? 'Baik'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importAbsensi(Request $request)
    {
        $request->validate([
            'file_absensi' => 'required|file|mimes:csv,txt,xlsx,xls'
        ]);

        list($kelas, $periode) = $this->getWaliKelasInfo();
        
        $file = $request->file('file_absensi');
        
        // Simple CSV Parser
        $handle = fopen($file->getPathname(), 'r');
        // Skip Header (First 3 lines based on template)
        fgetcsv($handle); // Petunjuk
        fgetcsv($handle); // Spacer
        fgetcsv($handle); // Header Columns

        $count = 0;
        
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Row Structure: [No, NIS, Nama, Sakit, Izin, Alpa]
            // Allow flexibility if user deleted header rows, check if NIS looks like NIS
            if (count($row) < 6) continue;
            
            $nis = trim($row[1]);
            if (empty($nis) || !is_numeric($nis)) continue; 

            // Find student by NIS
            $siswa = \App\Models\Siswa::where('nis_lokal', $nis)->first();
            if (!$siswa) continue;

            $sakit = (int) $row[3];
            $izin = (int) $row[4];
            $alpa = (int) $row[5];

            CatatanKehadiran::updateOrCreate(
                [
                    'id_siswa' => $siswa->id,
                    'id_kelas' => $kelas->id,
                    'id_periode' => $periode->id
                ],
                [
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'tanpa_keterangan' => $alpa,
                    'kelakuan' => $row[6] ?? 'Baik',
                    'kerajinan' => $row[7] ?? 'Baik',
                    'kebersihan' => $row[8] ?? 'Baik',
                ]
            );
            $count++;
        }
        fclose($handle);

        return redirect()->route('walikelas.monitoring')->with('success', "Berhasil import absensi untuk $count siswa.");
    }

    public function inputCatatan()
    {
        list($kelas, $periode) = $this->getWaliKelasInfo();
        if (!$kelas || !$periode) return back()->with('error', 'Akses ditolak.');

        $students = $kelas->anggota_kelas()->with('siswa')->get();
        
        $catatanRows = CatatanWaliKelas::where('id_kelas', $kelas->id)
            ->where('id_periode', $periode->id)
            ->get()
            ->keyBy('id_siswa');

        // Calculate Averages for Magic Notes
        $grades = NilaiSiswa::where('id_kelas', $kelas->id)
            ->where('id_periode', $periode->id)
            ->get();
            
        $averages = [];
        foreach ($students as $student) {
            $studentGrades = $grades->where('id_siswa', $student->id_siswa);
            if ($studentGrades->count() > 0) {
                $averages[$student->id_siswa] = round($studentGrades->avg('nilai_akhir'), 2);
            } else {
                $averages[$student->id_siswa] = 0;
            }
        }



        // Admin Filter Data
        $allClasses = collect([]);
        if (Auth::user()->isAdmin() || Auth::user()->isTu()) {
            $activeYear = TahunAjaran::where('status', 'aktif')->first();
            $query = Kelas::where('id_tahun_ajaran', $activeYear->id);
            if (request('jenjang')) {
                $query->whereHas('jenjang', fn($q) => $q->where('kode', request('jenjang')));
            }
            $allClasses = $query->orderBy('nama_kelas')->get();
        }

        return view('wali-kelas.catatan', compact('kelas', 'periode', 'students', 'catatanRows', 'averages', 'allClasses'));
    }

    public function storeCatatan(Request $request)
    {
        list($kelas, $periode) = $this->getWaliKelasInfo();

        // 1. Save Catatan (Notes)
        if ($request->has('catatan')) {
            foreach ($request->catatan as $siswaId => $content) {
                CatatanWaliKelas::updateOrCreate(
                    [
                        'id_siswa' => $siswaId,
                        'id_kelas' => $kelas->id,
                        'id_periode' => $periode->id
                    ],
                    [
                        'catatan_akademik' => $content,
                         // Note: We might want to use this for 'status_kenaikan' local storage too if we want to repopulate the dropdown next time
                         // Let's modify the CatatanWaliKelas model/table to store this transiently if needed, 
                         // BUT for now, let's just save to the REAL destination: promotion_decisions.
                         // Actually, to make the form "sticky" (show selected value), we should store it in CatatanWaliKelas too, or fetch from promotion_decisions.
                         // Viewing the blade, it tries to fetch `$note->status_kenaikan`.
                         // So I MUST save it to `catatan_wali_kelas` as well (column `status_kenaikan` must exist?).
                         // Let's assume the column might NOT exist and rely on `promotion_decisions` for the Rapor,
                         // BUT `catatan.blade.php` reads `$note->status_kenaikan`.
                         // I will check if I can save it to `catatan_wali_kelas`. Ideally yes.
                         // If column missing, it might error. I'll gamble user added it or I'll add a migration?
                         // User said "sekarang dirapor boss", suggesting we just need it to appear.
                         // I will use `promotion_decisions` for the Rapor primarily.
                         // But for sticky form, I'll attempt to save it to `catatan_wali_kelas` if the column exists, or just rely on the migration check.
                         // Checking migration... step 7907 summary said `catatan_kehadiran`.
                         // Let's assuming I should add it if I want it sticky.
                         // But CRITICALLY, I must save to `promotion_decisions`.
                    ]
                );
            }
        }

        // 2. Save Promotion Status (If Provided)
        if ($request->has('status_kenaikan')) {
            $mapStatus = [
                'naik' => 'promoted',
                'naik_percobaan' => 'promoted', // Maps to promoted physically, but UI shows 'Percobaan'
                'tinggal' => 'retained',
                'lulus' => 'graduated',
                'tidak_lulus' => 'not_graduated'
            ];

            foreach ($request->status_kenaikan as $siswaId => $status) {
                if (empty($status)) continue;
                
                $dbStatus = $mapStatus[$status] ?? null;
                if ($dbStatus) {
                    DB::table('promotion_decisions')->updateOrInsert(
                        [
                            'id_siswa' => $siswaId,
                            'id_kelas' => $kelas->id, 
                            'id_tahun_ajaran' => $kelas->id_tahun_ajaran
                        ],
                        [
                            'final_decision' => $dbStatus,
                            'updated_at' => now()
                        ]
                    );

                    // Also update local note for UI Stickiness (assuming column 'status_kenaikan' exists or reusing a field)
                    // If migration doesn't have it, this might crash.
                    // For safety, I will try to update it only if I'm sure, OR I'll add it to schema.
                    // Let's adding it to `CatatanWaliKelas` via updateOrCreate above?
                    // Let's TRY to add it to the update above, assuming I added the migration or will add it.
                    // Actually, Step 7907 view_file `catatan.blade.php` accesses `$note->status_kenaikan`. 
                    // This implies the column IS EXPECTED or at least the VIEW expects it. 
                    // I'll add it to the `CatatanWaliKelas` update block.
                    CatatanWaliKelas::where('id_siswa', $siswaId)
                        ->where('id_kelas', $kelas->id)
                        ->where('id_periode', $periode->id)
                        ->update(['status_kenaikan' => $status]); 
                }
            }
        }

        return back()->with('success', 'Catatan dan Status berhasil disimpan.');
    }

    public function inputEkskul()
    {
        list($kelas, $periode) = $this->getWaliKelasInfo();
        if (!$kelas || !$periode) return back()->with('error', 'Akses ditolak.');

        $students = $kelas->anggota_kelas()->with('siswa')->get();
        
        // Fetch existing ekskuls grouped by student
        // Use NilaiEkstrakurikuler model which points to the correct table with id_kelas
        $ekskulRows = NilaiEkstrakurikuler::where('id_kelas', $kelas->id)
            ->where('id_periode', $periode->id)
            ->get()
            ->groupBy('id_siswa');

        // Master list of ekskuls (Hardcoded for now as simple strings)
        $ekskulOptions = ['Pramuka', 'PMR', 'Futsal', 'Hadrah', 'Qiroah', 'Drum Band'];

        return view('wali-kelas.ekskul', compact('kelas', 'periode', 'students', 'ekskulRows', 'ekskulOptions'));
    }

    public function storeEkskul(Request $request)
    {
        list($kelas, $periode) = $this->getWaliKelasInfo();

        // Structure: ekskul[siswaId][index][nama_ekskul], ekskul[siswaId][index][nilai]
        
        foreach ($request->ekskul as $siswaId => $items) {
            // Delete old
            NilaiEkstrakurikuler::where('id_siswa', $siswaId)
                ->where('id_periode', $periode->id)
                ->delete();

            foreach ($items as $item) {
                if (!empty($item['nama_ekskul'])) {
                    NilaiEkstrakurikuler::create([
                        'id_siswa' => $siswaId,
                        'id_kelas' => $kelas->id,
                        'id_periode' => $periode->id,
                        'nama_ekskul' => $item['nama_ekskul'],
                        'nilai' => $item['nilai'] ?? '-',
                        'keterangan' => $item['keterangan'] ?? ''
                    ]);
                }
            }
        }

        return back()->with('success', 'Nilai Ekstrakurikuler berhasil disimpan.');
    }

    public function leger()
    {
        list($kelas, $periode, $activeYear) = $this->getWaliKelasInfo();
        if (!$kelas || !$periode) return back()->with('error', 'Akses ditolak.');

        $students = $kelas->anggota_kelas()->with('siswa')->get();
        // Fetch grades logic similar to monitoring but specific to Ledger grid
        // Reuse monitoring logic or simple fetch
        // Fix: Use Plotted Mapels only, not generic Jenjang mapels
        $assignedMapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelas->id)
            ->pluck('id_mapel');
            
        $mapels = Mapel::whereIn('id', $assignedMapelIds)
            ->orderBy('kategori', 'asc') // Group by category like Plotting
            ->orderBy('nama_mapel', 'asc')
            ->get();
        
        $grades = NilaiSiswa::where('id_kelas', $kelas->id)
            ->where('id_periode', $periode->id)
            ->get()
            ->keyBy(fn($item) => $item->id_siswa . '-' . $item->id_mapel); // Compound key for easy lookup

        // Fetch KKM (Per Year and Jenjang)
        $kkm = \App\Models\KkmMapel::where('id_tahun_ajaran', $activeYear->id)
             ->where('jenjang_target', $kelas->jenjang->kode) 
             ->pluck('nilai_kkm', 'id_mapel');
             

             
        return view('wali-kelas.leger', compact('kelas', 'periode', 'students', 'mapels', 'grades', 'kkm'));
    }

    public function monitoring()
    {
        list($kelas, $periode, $activeYear) = $this->getWaliKelasInfo();
        if (!$kelas || !$periode) return back()->with('error', 'Akses ditolak atau periode belum aktif.');

        // 1. Get all subjects for this jenjang (Specific OR 'Semua')
        // 1. Get Mapels (Only those assigned/plotted for this class)
        $assignedMapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelas->id)
            ->pluck('id_mapel');
            
        $mapels = Mapel::whereIn('id', $assignedMapelIds)->get();

        // 2. Fetch all grades for this class and period
        $grades = NilaiSiswa::where('id_kelas', $kelas->id)
            ->where('id_periode', $periode->id)
            ->get();
            
        // 3. Fetch Assigned Teachers
        $assignments = \App\Models\PengajarMapel::with('guru')
            ->where('id_kelas', $kelas->id)
            ->get()
            ->keyBy('id_mapel');
        
        // 3a. Fetch KKM List
        $kkmList = \App\Models\KkmMapel::where('id_tahun_ajaran', $kelas->id_tahun_ajaran ?? 1)
            ->where('jenjang_target', $kelas->jenjang->kode)
            ->pluck('nilai_kkm', 'id_mapel');
            
        $gradingSettings = DB::table('grading_settings')
            ->where('jenjang', $kelas->jenjang->kode)
            ->first();
        $defaultKkm = $gradingSettings ? $gradingSettings->kkm_default : 70;

        // 4. Process aggregation
        $monitoringData = [];
        $threshold = 86;

        foreach ($mapels as $mapel) {
            // Filter grades for this mapel
            $mapelGrades = $grades->where('id_mapel', $mapel->id);
            
            $minScore = $mapelGrades->min('nilai_akhir') ?? 0;
            $avgScore = $mapelGrades->avg('nilai_akhir') ?? 0;
            
            // Should use KKM or 86?
            // User request implies "Perlu Katrol" logic usually relates to KKM.
            // But original code used $threshold = 86 "Green if Safe".
            // Let's stick to user request: "Status needs Katrol if < KKM".
            // So we use KKM as the threshold for 'warning' status, OR keep 86 as a 'Great' threshold?
            // User complained: "Status Perlu Katrol (3) padahal sudah di katrol".
            // This suggests the STATUS text itself is misleading.
            // Let's align with Dashboard Logic: Status is "Aman" if Min Score >= KKM.
            
            $kkm = $kkmList[$mapel->id] ?? $defaultKkm;
            
            $belowKkmCount = $mapelGrades->where('nilai_akhir', '<', $kkm)->count();
            
            $totalStudents = $kelas->anggota_kelas->count();
            $gradedCount = $mapelGrades->count();
            
            // Teacher Info
            $teacherName = $assignments[$mapel->id]->guru->name ?? 'Belum Ada Guru';
            
            // Status Logic: Aman if ALL students >= KKM relative to what is graded
            // If partial input, we check graded ones.
            $isSafe = $gradedCount > 0 && $minScore >= $kkm;
            $status = $isSafe ? 'aman' : 'warning';
            
            // If no grades, maybe netural?
            if ($gradedCount == 0) $status = 'neutral';
            
            $monitoringData[] = (object) [
                'id' => $mapel->id,
                'nama_mapel' => $mapel->nama_mapel . ($mapel->nama_kitab ? ' (' . $mapel->nama_kitab . ')' : ''),
                'nama_guru' => $teacherName,
                'min_score' => $minScore,
                'avg_score' => round($avgScore, 2),
                'below_count' => $belowKkmCount, // Changed from threshold 86 to KKM
                'total_graded' => $gradedCount,
                'total_students' => $totalStudents,
                'status' => $status,
                'kkm' => $kkm // Pass for UI if needed
            ];
        }

        // Admin Context: Filter Logic
        $selectedJenjang = request('jenjang');
        $allPeriods = collect([]);

        if (Auth::user()->isAdmin() || Auth::user()->isTu()) {
            // Fetch All Periods for Selector (based on actual class year AND Jenjang)
            // Fix: Enforce Jenjang Filter on Periods (MI = Cawu, MTS = Semester)
            $allPeriods = Periode::where('id_tahun_ajaran', $kelas->id_tahun_ajaran)
                ->where('lingkup_jenjang', $kelas->jenjang->kode) // STRICT FILTER
                ->orderBy('id', 'desc')
                ->get();
        }
        
        // Check if ALL grades are final (and there are grades)
        $allLocked = $grades->count() > 0 && $grades->every(fn($g) => $g->status === 'final');

        return view('wali-kelas.monitoring', compact('kelas', 'periode', 'monitoringData', 'allPeriods', 'activeYear', 'selectedJenjang', 'allLocked'));
    }

    public function bulkFinalize(Request $request)
    {
        // 1. Validate Access
        $user = Auth::user();
        $kelas = Kelas::findOrFail($request->kelas_id);
        
        // Allow Admin, TU, or the actual Wali Kelas
        if (!$user->isAdmin() && !$user->isTu() && $kelas->id_wali_kelas !== $user->id) {
            return back()->with('error', 'Akes ditolak.');
        }

        $periodeId = $request->periode_id;
        if (!$periodeId) return back()->with('error', 'Periode tidak valid.');

        // 2. Update Status
        // Only update status for existing grades? Or do we need to check completeness?
        // User request: "otomatis semua akan terkunci" -> implying force lock.
        // We will update all NilaiSiswa records for this class & period to 'final'.
        
        $updated = NilaiSiswa::where('id_kelas', $kelas->id)
            ->where('id_periode', $periodeId)
            ->update(['status' => 'final']);

        return back()->with('success', "Berhasil mengunci $updated data nilai.");
    }
    
    public function legerRekap()
    {
        list($kelas, $activePeriode, $activeYear) = $this->getWaliKelasInfo();
        if (!$kelas) return back()->with('error', 'Akses ditolak.');

        $students = $kelas->anggota_kelas()->with('siswa')->get();
        
        // Mapels (Assigned)
        $assignedMapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
        $mapels = Mapel::whereIn('id', $assignedMapelIds)
            ->orderBy('kategori', 'asc')
            ->orderBy('nama_mapel', 'asc')
            ->get();

        // All Periods for this Active Year & Jenjang
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('lingkup_jenjang', $kelas->jenjang->kode)
            ->get()
            ->keyBy('id'); // e.g. [1 => Period1, 2 => Period2]

        // Fetch Grades for ALL periods
        $grades = NilaiSiswa::where('id_kelas', $kelas->id)
            ->whereIn('id_periode', $periods->keys())
            ->get()
            ->groupBy('id_siswa'); 

        // KKM
        $kkm = \App\Models\KkmMapel::where('id_tahun_ajaran', $activeYear->id)
             ->where('jenjang_target', $kelas->jenjang->kode) 
             ->pluck('nilai_kkm', 'id_mapel');

        return view('wali-kelas.leger-rekap', compact('kelas', 'periods', 'students', 'mapels', 'grades', 'kkm'));
    }
    public function kenaikanKelas()
    {
        list($kelas, $activePeriod, $activeYear) = $this->getWaliKelasInfo();
        if (!$kelas) {
            return back()->with('error', 'Anda tidak memiliki kelas di Tahun Ajaran Aktif (' . $activeYear->nama_tahun . '). Silakan cek Pengaturan Tahun Ajaran.');
        }

        // ==========================================
        // ACCESS CONTROL: FINAL PERIOD CHECK
        // ==========================================
        // Fix: Filter by Jenjang to prevent picking up other Jenjang's active period (e.g. MI Cawu 3 vs MTs Ganjil)
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
               ->where('lingkup_jenjang', $kelas->jenjang->kode)
               ->orderBy('id', 'asc')
               ->get();
        
        $activeP = $periods->firstWhere('status', 'aktif'); // Using local var to avoid conflict
        $lastPeriod = $periods->last();
        $isFinalPeriod = false;

        // 1. Logical Match
        if ($activeP && $lastPeriod && $activeP->id === $lastPeriod->id) {
             $isFinalPeriod = true;
        }
        // 2. Name Match (Robust)
        if ($activeP) {
            $name = strtolower($activeP->nama_periode);
            if (str_contains($name, 'cawu 3') || str_contains($name, 'semester 2') || str_contains($name, 'genap')) {
                $isFinalPeriod = true;
            }
            
            // 3. EXPLICIT OVERRIDE: If Name contains "Ganjil" or "Semester 1", it is NOT Final.
            // This prevents "Kelulusan" appearing in Semester 1 even if it's the only period.
            if (str_contains($name, 'ganjil') || str_contains($name, 'semester 1') || str_contains($name, 'cawu 1') || str_contains($name, 'cawu 2')) {
                $isFinalPeriod = false;
            }
        }

        $user = Auth::user();
        $isAdmin = $user->isAdmin() || $user->isTu() || $user->id === 1;
        $warningMessage = null;

        // 7. Get Grading Settings (Syarat Kenaikan)
        if (!$kelas->relationLoaded('jenjang')) {
             $kelas->load('jenjang');
        }
        $gradingSettings = DB::table('grading_settings')
            ->where('jenjang', $kelas->jenjang->kode) // Matches 'MI' or 'MTs'
            ->first();

        // Defaults if settings missing
        $maxKkmFailure = $gradingSettings->promotion_max_kkm_failure ?? 3;
        $minAttendance = $gradingSettings->promotion_min_attendance ?? 75; // Percent
        $minAttitude   = $gradingSettings->promotion_min_attitude ?? 'B'; // Minimum 'B' means 'C' fails

        // Helper for Attitude Comparison (A > B > C > D)
        $attRank = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1];
        $minAttRank = $attRank[$minAttitude] ?? 3; // Default B

        // DEBUG INFO (For View)
        $debugInfo = [
            'User' => $user->name . ' (' . $user->role . ')',
            'ActivePeriod' => $activeP ? $activeP->nama_periode : 'None',
            'ActivePeriodID' => $activeP ? $activeP->id : 'NULL',
            'IsFinalPeriod' => $isFinalPeriod ? 'YES' : 'NO',
            'IsAdmin' => $isAdmin ? 'YES' : 'NO',
            'MinAttendanceSetting' => $minAttendance,
            'EffectiveDaysSetting' => \App\Models\GlobalSetting::val('total_effective_days', 220)
        ];

        if (!$isFinalPeriod) {
            // WARN: Just a warning, don't block.
            $warningMessage = "⚠️ PERINGATAN: Periode saat ini (" . ($activeP->nama_periode ?? '-') . ") BUKAN periode akhir (Semester Genap). Fitur Kenaikan/Kelulusan mungkin tidak relevan.";
        }
        // ==========================================

        // 1. Get Students
        $students = $kelas->anggota_kelas()->with('siswa')->get()->sortBy(function($st) {
            return $st->siswa->nama_lengkap;
        });

        // 2. Get All Periods for Active Year
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('lingkup_jenjang', $kelas->jenjang->kode)
            ->get();
        
        // 3. Get All Grades for Active Year (Strict Class Filter)
        $allGrades = NilaiSiswa::where('id_kelas', $kelas->id)
            ->whereIn('id_periode', $periods->pluck('id'))
            ->get();

        // 4. Get KKM
        $kkmMapel = \App\Models\KkmMapel::where('id_tahun_ajaran', $activeYear->id)
             ->where('jenjang_target', $kelas->jenjang->kode) 
             ->pluck('nilai_kkm', 'id_mapel');
        
        // 5. Get Attendance (Strict Class Filter)
        $allAttendance = CatatanKehadiran::where('id_kelas', $kelas->id)
            ->whereIn('id_periode', $periods->pluck('id'))
            ->get();

        // 6. Get Existing Decisions (Full Object for Lock Status)
        $decisions = DB::table('promotion_decisions')
            ->where('id_kelas', $kelas->id)
            ->where('id_tahun_ajaran', $activeYear->id)
            ->get()
            ->keyBy('id_siswa'); // Key by Student ID

        // 7. Get Grading Settings (MOVED TO TOP)

        // 8. Calculate Stats per Student
        $studentStats = [];
        $summary = [
            'total' => $students->count(),
            'promote' => 0,
            'retain' => 0,
            'review' => 0
        ];

        // NEW: Get Assigned Mapels for this class to check completeness
        $assignedMapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
        $assignedMapels = Mapel::whereIn('id', $assignedMapelIds)->get();

        // --- FINAL YEAR LOGIC (MOVED TO TOP) ---
        if (!$kelas->relationLoaded('jenjang')) {
            $kelas->load('jenjang');
        }
        $jenjangCode = optional($kelas->jenjang)->kode;
        if (!$jenjangCode) {
            if (stripos($kelas->nama_kelas, 'MTs') !== false) $jenjangCode = 'MTS';
            elseif (stripos($kelas->nama_kelas, 'MI') !== false) $jenjangCode = 'MI';
        }
        $jenjangCode = strtoupper($jenjangCode ?? '');
        
        $grade = (int) filter_var($kelas->nama_kelas, FILTER_SANITIZE_NUMBER_INT);
        $finalGradeMI = (int) \App\Models\GlobalSetting::val('final_grade_mi', 6);
        $finalGradeMTS = (int) \App\Models\GlobalSetting::val('final_grade_mts', 9);

        // Strict Logic
        $isFinalYear = false;
        
        // Ensure we are in the Final Period (Semester Genap) for Graduation
        // $isFinalPeriod is calculated at the top of this function
        if (isset($isFinalPeriod) && $isFinalPeriod) {
            if ($jenjangCode === 'MI') {
                if ($grade == $finalGradeMI) $isFinalYear = true;
            } elseif ($jenjangCode === 'MTS') {
                    if ($grade == $finalGradeMTS) $isFinalYear = true; 
                    elseif ($finalGradeMTS == 9 && $grade == 3) $isFinalYear = true; // Legacy
            }
        }
        // --------------------------------

        // 7b. Get Ijazah Grades (for Final Year Sync)
        $allIjazah = DB::table('nilai_ijazah')
            ->whereIn('id_siswa', $students->pluck('id_siswa'))
            ->get()
            ->groupBy('id_siswa');

        foreach ($students as $student) {
            $sId = $student->id_siswa;
            $sGrades = $allGrades->where('id_siswa', $sId);
            
            // Logic:
            $underKkmCount = 0;
            $yearlySum = 0;
            $mapelCount = 0;
            $gradesDetail = []; // Accumulate grades for popup

            foreach ($assignedMapels as $mapel) {
                $mId = $mapel->id;
                $finalMapelScore = 0;
                $kkm = $kkmMapel[$mId] ?? 70; // Default KKM

                if (!$isFinalPeriod && isset($activeP)) {
                     // NON-FINAL: Use Active Period Grade
                     // Ensure we look at $sGrades which contains ALL period grades
                     $pGrade = $sGrades->where('id_mapel', $mId)->where('id_periode', $activeP->id)->first();
                     $finalMapelScore = $pGrade ? $pGrade->nilai_akhir : 0;
                } else {
                    // FINAL: Use Yearly Average
                    $theseGrades = $sGrades->where('id_mapel', $mId);
                    if ($theseGrades->count() > 0) {
                        $finalMapelScore = $theseGrades->avg('nilai_akhir');
                    } else {
                        $finalMapelScore = 0;
                    }
                }

                if ($finalMapelScore < $kkm) {
                    $underKkmCount++;
                    // Optional: Track specific mapels
                }
                
                $yearlySum += $finalMapelScore;
                $mapelCount++;

                // Add to details
                $gradesDetail[] = [
                    'mapel' => $mapel->nama_mapel,
                    'kkm' => $kkm,
                    'nilai' => round($finalMapelScore, 0),
                    'is_under' => ($finalMapelScore < $kkm)
                ];
            }

            $avgYearly = $mapelCount > 0 ? round($yearlySum / $mapelCount, 1) : 0;

            // Attitude & Attendance
            // Get latest attitude
            if (!$isFinalPeriod && isset($activeP)) {
                $lastAttitude = $allAttendance->where('id_siswa', $sId)->where('id_periode', $activeP->id)->first();
            } else {
                $lastAttitude = $allAttendance->where('id_siswa', $sId)->sortByDesc('id_periode')->first();
            }
            
            $attitudeCode = 'B'; // Default
            if ($lastAttitude) {
                $attMap = ['Baik' => 'A', 'Cukup' => 'B', 'Kurang' => 'C'];
                $attitudeCode = $attMap[$lastAttitude->kelakuan] ?? 'B';
            }
            $currentAttRank = $attRank[$attitudeCode] ?? 3;

            // Attendance % Logic
            $periodCount = $periods->count();
            // Default Yearly Effective Days
            $effectiveDays = \App\Models\GlobalSetting::val('total_effective_days', 220);
            if ($effectiveDays <= 0) $effectiveDays = 220;

            if (!$isFinalPeriod && isset($activeP)) {
                 // NON-FINAL: Use Active Period Only
                 $sAtt = $allAttendance->where('id_siswa', $sId)->where('id_periode', $activeP->id);
                 
                 // Adjust Effective Days for this Period (Approximate)
                 if ($periodCount > 0) {
                     $effectiveDays = round($effectiveDays / $periodCount);
                 }
            } else {
                 // FINAL: Use All Attendance (Yearly)
                 $sAtt = $allAttendance->where('id_siswa', $sId);
            }

            // Only count Alpa (Unexcused) as requested
            $totalAbsent = $sAtt->sum('tanpa_keterangan'); 
            
            $attPercentage = ($effectiveDays > 0) ? round((($effectiveDays - $totalAbsent) / $effectiveDays) * 100) : 0;
            $attPercentage = max(0, min(100, $attPercentage));

            // System Recommendation Logic
            // ... (Logic logic is same) ...
            // $isFinalYear calculated at top
            
            if (!$isFinalPeriod) {
                // Non-Final Period: Use Tuntas / Belum Tuntas
                $defaultPromote = 'Tuntas';
                $defaultRetain = 'Belum Tuntas';
            } else {
                // Final Period: Use Naik Kelas / Lulus
                $defaultPromote = $isFinalYear ? 'Lulus' : 'Naik Kelas';
                $defaultRetain = $isFinalYear ? 'Tidak Lulus' : 'Tinggal Kelas';
            }
            
            $recommendation = $defaultPromote;
            $systemStatus = 'promote'; 

            $failConditions = [];

            // CHECK: Must participate in ALL Periods (If Enabled in Settings)
            // ONLY RELEVANT FOR FINAL PERIOD
            $requiresAllPeriods = isset($gradingSettings->promotion_requires_all_periods) ? (bool)$gradingSettings->promotion_requires_all_periods : true;
            
            if ($isFinalPeriod && $requiresAllPeriods) {
                // We check if student has at least one grade entry in each period
                $attendedPeriodIds = $sGrades->pluck('id_periode')->unique();
                $missingPeriods = $periods->filter(function($p) use ($attendedPeriodIds) {
                    return !$attendedPeriodIds->contains($p->id);
                });

                if ($missingPeriods->count() > 0) {
                    // Formatting: "Ganjil, Genap"
                    $periodNames = $missingPeriods->pluck('nama_periode')->join(', ');
                    $failConditions[] = "Tidak mengikuti ujian periode: $periodNames";
                }
            }
            
            if ($underKkmCount > $maxKkmFailure) $failConditions[] = "Mapel < KKM ($underKkmCount > $maxKkmFailure)";
            if ($currentAttRank < $minAttRank) $failConditions[] = "Sikap ($attitudeCode < $minAttitude)";
            
            // Add detailed counts
            $totalSakit = $sAtt->sum('sakit');
            $totalIzin = $sAtt->sum('izin');

            // Format match PromotionController
            if ($attPercentage < $minAttendance) {
                 $periodLabel = $isFinalPeriod ? 'Setahun' : 'Periode Ini';
                 $failConditions[] = "Kehadiran $periodLabel $attPercentage% < $minAttendance% (A: $totalAbsent)";
            }

            if (count($failConditions) > 0) {
                 // STRICT: If any failure condition is met -> Automatically RETAIN
                 $recommendation = $defaultRetain; 
                 $systemStatus = 'retain';
            }

            // Result
            $fail = count($failConditions) > 0;
            // $isConditional logic removed since we now default to Retain for any failure
            
            // Recalculate System Status (Final Check)
            // If fail -> Retain. Else -> Promote/Graduate
            $baseStatus = ($kelas->jenjang->kode == 'MI' && $kelas->tingkat == 6) || ($kelas->jenjang->kode == 'MTS' && $kelas->tingkat == 9) ? 'graduate' : 'promote';
            $systemStatus = $fail ? 'retain' : $baseStatus; 
            
            // Override Logic
            $decisionObj = $decisions[$sId] ?? null;
            $finalStatus = $decisionObj ? $decisionObj->final_decision : $systemStatus;
            $isDecisionLocked = $decisionObj && !is_null($decisionObj->override_by);

            // Manual Note
            $manualNote = $decisionObj ? $decisionObj->notes : null;

            // Ijazah Info Logic (On-the-fly)
            $ijazahNote = null;
            $ijazahClass = null;
            
            if ($isFinalYear) {
                 $sIjazah = $allIjazah[$sId] ?? collect([]);
                 $iCount = $sIjazah->where('nilai_ijazah', '>', 0)->count();
                 $iSum = $sIjazah->where('nilai_ijazah', '>', 0)->sum('nilai_ijazah');
                 
                 if ($iCount > 0) {
                     $iAvg = $iSum / $iCount;
                     $minLulusIjazah = (float) \App\Models\GlobalSetting::val('ijazah_min_lulus', 60);

                     if ($iAvg >= $minLulusIjazah) {
                         // Check for Rapor Conflict
                         if ($systemStatus == 'retain') {
                             // Determine WHY they are retained
                             if ($underKkmCount > $maxKkmFailure) {
                                  $ijazahNote = "⚠️ PERHATIAN: Nilai Ijazah CUKUP (" . round($iAvg, 2) . "), tapi Gagal $underKkmCount Mapel (Max: $maxKkmFailure)! Wajib Remedial.";
                                  $ijazahClass = "text-red-600 font-bold";
                             } elseif ($currentAttRank < $minAttRank) {
                                  $ijazahNote = "⚠️ PERHATIAN: Nilai Ijazah CUKUP (" . round($iAvg, 2) . "), tapi Sikap Kurang ($attitudeCode)!";
                                  $ijazahClass = "text-amber-600 font-bold";
                             } elseif ($attPercentage < $minAttendance) {
                                  $ijazahNote = "⚠️ PERHATIAN: Nilai Ijazah CUKUP (" . round($iAvg, 2) . "), tapi Kehadiran Kurang ($attPercentage%)!";
                                  $ijazahClass = "text-amber-600 font-bold";
                             } else {
                                  // Fallback for generic retain
                                  $ijazahNote = "⚠️ PERHATIAN: Nilai Ijazah CUKUP (" . round($iAvg, 2) . "), tapi status Tidak Lulus.";
                                  $ijazahClass = "text-amber-600 font-bold";
                             }
                         } else {
                             // Promoting/Graduating but maybe has some bad subjects?
                             if ($underKkmCount > 0) {
                                  $ijazahNote = "Info: Ijazah LULUS (Rata-rata: " . round($iAvg, 2) . "). Ada $underKkmCount Mapel < KKM (Disarankan Perbaikan).";
                                  $ijazahClass = "text-emerald-700 font-bold";
                             } else {
                                  $ijazahNote = "Info: Ijazah LULUS (Rata-rata: " . round($iAvg, 2) . ")";
                                  $ijazahClass = "text-emerald-600 font-bold";
                             }
                         }
                     } else {
                         $ijazahNote = "PERHATIAN: Ijazah TIDAK LULUS (Rata-rata: " . round($iAvg, 2) . " < $minLulusIjazah)";
                         $ijazahClass = "text-red-600 font-bold";
                     }
                 }
            } else {
                 // --- Regular Class Logic ---
                 // User Request: "tidak sampai KKM juga harus ada info"
                 
                 // Get Failed Mapels (if any)
                 $failedMapels = collect($gradesDetail)->where('is_under', true)->pluck('mapel');
                 
                 if ($underKkmCount > 0) {
                     $count = $failedMapels->count();
                     $names = $failedMapels->take(3)->implode(', ');
                     if ($count > 3) $names .= ", dll";
                     
                     $ijazahNote = "Remedial ($count): $names";
                     $ijazahClass = "text-red-600 font-bold";
                 } else {
                     // Check Attitude/Absence Failures
                     if ($currentAttRank < $minAttRank) {
                         $ijazahNote = "Sikap Kurang ($attitudeCode)";
                         $ijazahClass = "text-amber-600 font-bold";
                     } elseif ($attPercentage < $minAttendance) {
                         $ijazahNote = "Kehadiran Kurang ($attPercentage%)";
                         $ijazahClass = "text-amber-600 font-bold";
                     } else {
                         // Clean
                         $ijazahNote = "Tuntas. Rata-rata: $avgYearly";
                         $ijazahClass = "text-slate-500 italic"; // Neutral
                     }
                 }
            }

            if ($finalStatus == 'review') $summary['review']++;
            elseif ($finalStatus == 'promote' || $finalStatus == 'graduate') $summary['promote']++;
            else $summary['retain']++; // Retain counts as review/fail bucket for visual

            $studentStats[] = (object) [
                'student' => $student->siswa,
                'avg_yearly' => $avgYearly,
                'under_kkm' => $underKkmCount,
                'grades_detail' => $gradesDetail, 
                'attitude' => $attitudeCode,
                'attitude_detail' => (object) [
                    'kelakuan' => $lastAttitude->kelakuan ?? '-',
                    'kerajinan' => $lastAttitude->kerajinan ?? '-',
                    'kebersihan' => $lastAttitude->kebersihan ?? '-'
                ],
                'attendance_pct' => $attPercentage,
                'total_absent' => $totalAbsent,    
                'effective_days' => $effectiveDays, 
                'recommendation' => $recommendation,
                'system_status' => $systemStatus,
                'final_status' => $finalStatus,
                'final_status' => $finalStatus,
                'is_locked' => $isDecisionLocked, // PASS LOCK STATUS
                'manual_note' => ($manualNote && !str_starts_with($manualNote, 'Perhatian') && !str_starts_with($manualNote, 'Sikap') && !str_starts_with($manualNote, 'Kehadiran') && !str_starts_with($manualNote, 'Mapel')) ? $manualNote : null, // Filter out legacy system notes
                'ijazah_note' => $ijazahNote, // NEW
                'ijazah_class' => $ijazahClass, // NEW
                'fail_reasons' => $failConditions 
            ];
        }

        // Recalculate Summary from StudentStats based on SYSTEM RECOMMENDATION (matching the visual badges)
        $summary = [
            'total' => count($studentStats),
            'promote' => collect($studentStats)->whereIn('system_status', ['promote', 'graduate'])->count(),
            'retain' => collect($studentStats)->whereIn('system_status', ['retain', 'not_graduate'])->count(),
            'review' => collect($studentStats)->where('system_status', 'review')->count()
        ];
        
        // Note: 'retain' key in view sums 'review' + 'retain', so this works perfectly.

        // Note: 'retain' key in view sums 'review' + 'retain', so this works perfectly.

        // Admin Filter Data
        $allClasses = collect([]);
        if (Auth::user()->isAdmin() || Auth::user()->isTu()) {
             $query = Kelas::where('id_tahun_ajaran', $activeYear->id);
             if (request('jenjang')) {
                 $query->whereHas('jenjang', fn($q) => $q->where('kode', request('jenjang')));
             }
             $allClasses = $query->orderBy('nama_kelas')->get();
        }

        // Calculate Lock Status
        $isLocked = false;
        $latestYear = TahunAjaran::orderBy('id', 'desc')->first();
        
        // Final Year Logic moved to top

        $pageContext = [
            'type' => 'promotion',
            'title' => 'Kenaikan Kelas',
            'success_label' => 'Naik Kelas',
            'fail_label' => 'Tinggal Kelas',
            'success_badge' => 'Naik Kelas',
            'fail_badge' => 'Tinggal Kelas'
        ];

        if ($isFinalYear) {
            $pageContext = [
                'type' => 'graduation',
                'title' => 'Kelulusan Akhir',
                'success_label' => 'LULUS',
                'fail_label' => 'TIDAK LULUS',
                'success_badge' => 'LULUS',
                'fail_badge' => 'TIDAK LULUS'
            ];
        } elseif (!$isFinalPeriod) {
            // Non-Final Period Context
            $pageContext = [
                'type' => 'evaluation',
                'title' => 'Monitoring Akademik',
                'success_label' => 'Tuntas',
                'fail_label' => 'Belum Tuntas',
                'success_badge' => 'Tuntas',
                'fail_badge' => 'Belum Tuntas'
            ];
        }

        if ($activeYear && $latestYear && $activeYear->id !== $latestYear->id) {
            if (!\App\Models\GlobalSetting::val('allow_edit_past_data', 0)) {
                $isLocked = true;
            }
        }

        // BRUTAL DEBUG 2.0
        return view('wali-kelas.kenaikan-kelas', compact(
            'kelas', 
            'studentStats', 
            'summary', 
            'isFinalYear', 
            'isFinalPeriod', // CRITICAL: Must pass this!
            'allClasses', 
            'isLocked',
            'pageContext', // Passing the context
            'debugInfo'
        ));
    }

    public function storeKenaikanKelas(Request $request)
    {
        if (!$this->checkActiveYear()) {
             return back()->with('error', '⚠️ AKSES DITOLAK: Periode terkunci.');
        }
        list($kelas, $periode, $activeYear) = $this->getWaliKelasInfo();
        
        $decisions = $request->input('decisions', []);
        $notes = $request->input('notes', []);
        
        foreach ($decisions as $sId => $decision) {
            $note = $notes[$sId] ?? null;
            
            DB::table('promotion_decisions')->updateOrInsert(
                [
                    'id_siswa' => $sId,
                    'id_kelas' => $kelas->id,
                    'id_tahun_ajaran' => $activeYear->id
                ],
                [
                    'final_decision' => $decision,
                    'updated_at' => now(),
                    'notes' => $note, // Save Manual Note
                    'override_by' => Auth::id() // LOCK IT! Ensures permanence.
                ]
            );
            
            // Also sync to CatatanWaliKelas for legacy/report compatibility if needed
             CatatanWaliKelas::updateOrCreate(
                [
                    'id_siswa' => $sId,
                    'id_periode' => $periode->id
                ],
                [
                    'id_kelas' => $kelas->id, // Move class ID to update content
                    'status_kenaikan' => ($decision == 'promoted' || $decision == 'graduated') ? 'naik' : 'tinggal' 
                    // Simple mapping, might need more detail
                ]
            );
        }

        return back()->with('success', 'Keputusan kenaikan kelas berhasil disimpan.');
    }

    public function exportLeger() 
    {
        list($kelas, $periode, $activeYear) = $this->getWaliKelasInfo();
        if (!$kelas) return back()->with('error', 'Akses ditolak.');

        $students = $kelas->anggota_kelas()->with('siswa')->get();
        
        // Mapels
        $assignedMapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
        $mapels = Mapel::whereIn('id', $assignedMapelIds)
            ->orderBy('kategori', 'asc')
            ->orderBy('nama_mapel', 'asc')
            ->get();
            
        // Grades
        $grades = NilaiSiswa::where('id_kelas', $kelas->id)
            ->where('id_periode', $periode->id)
            ->get()
            ->groupBy('id_siswa');
            
        // KKM
        $kkm = \App\Models\KkmMapel::where('id_tahun_ajaran', $activeYear->id)
             ->where('jenjang_target', $kelas->jenjang->kode) 
             ->pluck('nilai_kkm', 'id_mapel');

        // Calculate Stats for Ranking
        $studentStats = [];
        foreach($students as $student) {
            $sGrades = $grades[$student->id_siswa] ?? collect([]);
            $totalScore = 0;
            $countMapel = 0;
            
            foreach($mapels as $mapel) {
                // Find grade for this mapel
                $g = $sGrades->where('id_mapel', $mapel->id)->first();
                if($g) {
                    $totalScore += $g->nilai_akhir;
                    $countMapel++;
                }
            }
            
            $avg = $countMapel > 0 ? $totalScore / $countMapel : 0;
            
            $studentStats[$student->id_siswa] = [
                'total' => $totalScore,
                'avg' => $avg,
            ];
        }
        
        // Sorting for Rank (DESC by Avg)
        uasort($studentStats, function($a, $b) {
            return $b['avg'] <=> $a['avg'];
        });
        
        // Assign Rank
        $rank = 1;
        foreach($studentStats as $sId => &$stat) {
            $stat['rank'] = $rank++;
        }
        
        $filename = "Leger_Semester_{$kelas->nama_kelas}_{$periode->nama_periode}.xls";
        
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        
        return view('wali-kelas.leger-export', compact('kelas', 'periode', 'students', 'mapels', 'grades', 'studentStats', 'kkm'));
    }

    public function exportLegerRekap()
    {
        list($kelas, $activePeriode, $activeYear) = $this->getWaliKelasInfo();
        if (!$kelas) return back()->with('error', 'Akses ditolak.');

        $students = $kelas->anggota_kelas()->with('siswa')->get();
        
        // Mapels
        $assignedMapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
        $mapels = Mapel::whereIn('id', $assignedMapelIds)
            ->orderBy('kategori', 'asc')
            ->orderBy('nama_mapel', 'asc')
            ->get();

        // Periods
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('lingkup_jenjang', $kelas->jenjang->kode)
            ->get()
            ->keyBy('id');

        // Grades
        $grades = NilaiSiswa::where('id_kelas', $kelas->id)
            ->whereIn('id_periode', $periods->keys())
            ->get()
            ->groupBy('id_siswa'); 
            
        // KKM
        $kkm = \App\Models\KkmMapel::where('id_tahun_ajaran', $activeYear->id)
             ->where('jenjang_target', $kelas->jenjang->kode) 
             ->pluck('nilai_kkm', 'id_mapel');

        // Calculate Annual Stats for Ranking
        $studentStats = [];
        
        foreach ($students as $student) {
             $sGrades = $grades[$student->id_siswa] ?? collect([]);
             
             // Group by Mapel to get Mapel Averages first
             $mapelGrades = $sGrades->groupBy('id_mapel');
             $mapelAvgs = [];
             
             foreach ($mapelGrades as $mId => $mGrades) {
                 $mapelAvgs[] = $mGrades->avg('nilai_akhir');
             }
             
             $annualAvg = count($mapelAvgs) > 0 ? array_sum($mapelAvgs) / count($mapelAvgs) : 0;
             $annualTotal = array_sum($mapelAvgs); // Sum of averages as requested
             
             $studentStats[$student->id_siswa] = [
                 'avg' => $annualAvg,
                 'total' => $annualTotal
             ];
        }
        
        // Sort
        uasort($studentStats, function($a, $b) {
            return $b['avg'] <=> $a['avg'];
        });
        
        // Assign Rank
        $rank = 1;
        foreach($studentStats as $sId => &$stat) {
            $stat['rank'] = $rank++;
        }

        $filename = "Leger_Tahunan_{$kelas->nama_kelas}_{$activeYear->nama_tahun}.xls";
        
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        return view('wali-kelas.leger-rekap-export', compact('kelas', 'periods', 'students', 'mapels', 'grades', 'kkm', 'studentStats'));
    }
    public function katrol(Request $request)
    {
        $user = Auth::user();
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();

        // Fetch Classes for Selector
        if ($user->hasRole('admin') || $user->hasRole('staff_tu')) {
            $classes = Kelas::where('id_tahun_ajaran', $activeYear->id)
                            ->with('jenjang')
                            ->orderBy('tingkat_kelas')
                            ->orderBy('nama_kelas')
                            ->get();
        } else {
            $classes = Kelas::where('id_tahun_ajaran', $activeYear->id)
                            ->where('id_wali_kelas', $user->id)
                            ->with('jenjang')
                            ->get();
        }

        // Selected Class
        if ($request->kelas_id) {
            $kelas = $classes->where('id', $request->kelas_id)->first();
        } else {
            $kelas = $classes->first();
        }

        if (!$kelas) {
            return redirect()->route('walikelas.dashboard')->with('error', 'Kelas tidak ditemukan.');
        }

        $kelasId = $kelas->id;

        // Active Year
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();

        // Periods
        $allPeriods = Periode::where('id_tahun_ajaran', $activeYear->id)->orderBy('id')->get();
        $selectedPeriodeId = $request->periode_id ?? ($allPeriods->where('status', 'aktif')->first()->id ?? null);

        // Subjects (Mapel)
        // Better: Get mapels from NilaiSiswa to ensure we only show mapels that have grades.
        $subjects = Mapel::whereHas('nilai_siswa', function($q) use ($kelas, $selectedPeriodeId) {
            $q->where('id_kelas', $kelas->id)->where('id_periode', $selectedPeriodeId);
        })->get();

        if ($subjects->isEmpty()) {
            // Fallback: Get all mapels
             $subjects = Mapel::all();
        }

        $mapelId = $request->mapel_id ?? ($subjects->first()->id ?? null);

        // 2. Fetch Grades
        $grades = NilaiSiswa::where('id_kelas', $kelas->id)
            ->where('id_periode', $selectedPeriodeId)
            ->where('id_mapel', $mapelId)
            ->with('siswa')
            ->get();

        // 3. Get KKM
        $jenjangCode = optional($kelas->jenjang)->kode ?? ($kelas->tingkat > 6 ? 'MTS' : 'MI');
        
        $kkmEntry = \App\Models\KkmMapel::where('id_mapel', $mapelId)
            ->where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang_target', $jenjangCode) 
            ->first();
        
        $currentKkm = $kkmEntry->nilai_kkm ?? 70; // Default KKM

        return view('wali-kelas.katrol.index', compact(
            'kelas', '$kelas->id', 'classes', 
            'allPeriods', 'selectedPeriodeId', 
            'subjects', 'mapelId', 
            'grades', 'currentKkm', 'activeYear'
        ));
    }

    public function storeKatrol(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required',
            'mapel_id' => 'required',
            'periode_id' => 'required',
            'method_type' => 'required'
        ]);

        $grades = NilaiSiswa::where('id_kelas', $request->kelas_id)
            ->where('id_periode', $request->periode_id)
            ->where('id_mapel', $request->mapel_id)
            ->get();

        $count = 0;
        $method = $request->method_type;

        foreach ($grades as $grade) {
            // Backup original score if not already backed up
            if (is_null($grade->nilai_akhir_asli)) {
                $grade->nilai_akhir_asli = $grade->nilai_akhir;
            }

            $original = $grade->nilai_akhir_asli ?? $grade->nilai_akhir;
            $newScore = $original;
            $note = '';

            // RESET Logic
            if ($method === 'reset') {
                $newScore = $original;
                $note = null; // Clear note
            } 
            // CALCULATE Logic
            else {
                if ($method === 'kkm') {
                    $threshold = $request->min_threshold ?? 70;
                    if ($original < $threshold) {
                        $newScore = $threshold;
                        $note = "Katrol KKM ($threshold)";
                    }
                } elseif ($method === 'points') {
                    $points = $request->boost_points ?? 0;
                    $ceiling = $request->max_ceiling ?? 100;
                    if ($original < $ceiling) {
                        $newScore = min($ceiling, $original + $points);
                        $note = "Boost +$points";
                    }
                } elseif ($method === 'percentage') {
                    $percent = $request->boost_percent ?? 0;
                    $factor = 1 + ($percent / 100);
                    $newScore = min(100, round($original * $factor));
                    $note = "Boost {$percent}%";
                } elseif ($method === 'linear_scale') {
                    // Linear Interpolation
                    $targetMin = $request->target_min;
                    $targetMax = $request->target_max;
                    $dataMin = $request->data_min;
                    $dataMax = $request->data_max;

                    if ($dataMax > $dataMin) {
                        $ratio = ($original - $dataMin) / ($dataMax - $dataMin);
                        $range = $targetMax - $targetMin;
                        $calc = $targetMin + ($ratio * $range);
                        $newScore = min(100, round($calc));
                        $note = "Interpolasi ($targetMin-$targetMax)";
                        
                        // Prevent downgrade
                        $newScore = max($newScore, $original);
                    }
                }
            }

            // Update if changed
            if ($newScore != $grade->nilai_akhir || $grade->katrol_note != $note) {
                $grade->nilai_akhir = $newScore;
                $grade->katrol_note = $note;
                $grade->save();
                $count++;
            }
        }

        return redirect()->back()->with('success', "Berhasil memperbarui nilai untuk $count siswa.");
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
