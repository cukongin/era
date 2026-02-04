<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\NilaiSiswa;
use App\Models\Mapel; 

class TuController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // Stats
        $totalSiswa = Siswa::where('status_siswa', 'aktif')->count();
        $totalKelas = Kelas::where('id_tahun_ajaran', $activeYear->id)->count();
        
        // --- Cohort Analysis (Grafik Angkatan) ---
        // 1. Get List of Available "Angkatan" (Base it on Tahun Ajaran list)
        $availableAngkatan = TahunAjaran::orderBy('id', 'desc')->get();
        
        // 2. Determine Selected Angkatan (Default to 3 years ago or active year)
        // If no selection, pick reasonable default (e.g. 2023/2024 or latest logic)
        $selectedAngkatanId = $request->get('angkatan_id', $activeYear->id);
        $selectedAngkatan = $availableAngkatan->find($selectedAngkatanId);

        // 3. Logic: Find students who were in Tingkat 1 (MI) or Tingkat 7 (MTs) in that Year
        // If we can't find exact entry data, we check for enrollment in that year with lowest level.
        $startLevel = 1; // Default MI
        // You could add a filter for Jenjang later. For now assume MI/Integrated.

        // Get Class IDs for that Year + Level 1
        $cohortClassIds = Kelas::where('id_tahun_ajaran', $selectedAngkatanId)
            ->where('tingkat_kelas', $startLevel) // Grab Level 1 classes
            ->pluck('id');
            
        // Get Students in those classes (The Cohort Members)
        $cohortStudentIds = \App\Models\AnggotaKelas::whereIn('id_kelas', $cohortClassIds)
            ->pluck('id_siswa');
            
        // 4. Trace their grades over years
        // We need all years starting from Selected Angkatan
        $futureYears = TahunAjaran::where('id', '>=', $selectedAngkatanId)->orderBy('id', 'asc')->get();
        
        $chartLabels = [];
        $chartData = [];
        $cohortDescription = "Data Angkatan Masuk " . ($selectedAngkatan->nama ?? '-');

        if ($cohortStudentIds->isEmpty()) {
            // Fallback: If no cohort found (maybe user selected a future year or empty year), show zeros or try simpler query
            // Attempt: Just show school average for those years if cohort is empty to avoid broken chart
             foreach($futureYears as $yr) {
                $chartLabels[] = $yr->nama_tahun . " (Thn ke-" . ($loop->iteration) . ")";
                $chartData[] = 0;
             }
             $cohortDescription .= " (Data Siswa Tidak Ditemukan)";
        } else {
             $currentLevel = $startLevel;
             foreach($futureYears as $yr) {
                $lbl = "Kelas " . $currentLevel . " (" . $yr->nama_tahun . ")";
                
                $avg = NilaiSiswa::whereIn('id_siswa', $cohortStudentIds)
                    ->whereHas('kelas', fn($q) => $q->where('id_tahun_ajaran', $yr->id))
                    ->avg('nilai_akhir');
                    
                $chartLabels[] = $lbl;
                $val = $avg ? round($avg, 2) : 0;
                $chartData[] = $val;
                
                $currentLevel++; 
             }
             $cohortDescription .= " (" . $cohortStudentIds->count() . " Siswa)";
        }

        // --- EXTRAS: Best Student, Best Class, Trend ---
        // (Existing Top Student & Best Class logic...)
        $topStudent = null;
        if ($cohortStudentIds->isNotEmpty()) {
            $bestSiswaRaw = NilaiSiswa::whereIn('id_siswa', $cohortStudentIds)
                ->selectRaw('id_siswa, AVG(nilai_akhir) as average_score')
                ->groupBy('id_siswa')
                ->orderByDesc('average_score')
                ->first();
                
            if ($bestSiswaRaw) {
                $s = Siswa::find($bestSiswaRaw->id_siswa);
                // Safe access to relation
                $anggotaKelas = $s->kelas_saat_ini; 
                $className = ($anggotaKelas && $anggotaKelas->kelas) ? $anggotaKelas->kelas->nama_kelas : '-';
                
                $topStudent = [
                    'name' => $s->nama_lengkap,
                    'score' => round($bestSiswaRaw->average_score, 2),
                    'class' => $className
                ];
            }
        }

        $bestClass = null;
        if ($cohortStudentIds->isNotEmpty()) {
            $lastYearWithData = TahunAjaran::where('id', '>=', $selectedAngkatanId)->orderBy('id', 'desc')->first();
            $bestClassRaw = NilaiSiswa::whereHas('kelas', function($q) use ($lastYearWithData) {
                    $q->where('id_tahun_ajaran', $lastYearWithData->id);
                })
                ->whereIn('id_siswa', $cohortStudentIds)
                ->join('kelas', 'nilai_siswa.id_kelas', '=', 'kelas.id')
                ->selectRaw('kelas.nama_kelas, AVG(nilai_siswa.nilai_akhir) as average_score')
                ->groupBy('kelas.nama_kelas')
                ->orderByDesc('average_score')
                ->first();

            if ($bestClassRaw) {
                $bestClass = [
                    'name' => $bestClassRaw->nama_kelas,
                    'score' => round($bestClassRaw->average_score, 2)
                ];
            }
        }

        $trend = 'stable';
        if (count($chartData) >= 2) {
            $last = end($chartData);
            $prev = prev($chartData);
            if ($last > $prev) $trend = 'up';
            elseif ($last < $prev) $trend = 'down';
        }

        // --- NEW: Subject Analysis ---
        $hardestSubjects = [];
        $easiestSubjects = [];
        $gradeDistribution = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];

        if ($cohortStudentIds->isNotEmpty()) {
            // Hardest (Lowest Avg)
            $hardestSubjects = NilaiSiswa::whereIn('id_siswa', $cohortStudentIds)
                ->join('mapel', 'nilai_siswa.id_mapel', '=', 'mapel.id')
                ->select('mapel.nama_mapel', \DB::raw('AVG(nilai_siswa.nilai_akhir) as avg_score'))
                ->groupBy('mapel.nama_mapel')
                ->orderBy('avg_score', 'asc')
                ->take(5)
                ->get(); // Collection

            // Easiest (Highest Avg)
            $easiestSubjects = NilaiSiswa::whereIn('id_siswa', $cohortStudentIds)
                ->join('mapel', 'nilai_siswa.id_mapel', '=', 'mapel.id')
                ->select('mapel.nama_mapel', \DB::raw('AVG(nilai_siswa.nilai_akhir) as avg_score'))
                ->groupBy('mapel.nama_mapel')
                ->orderBy('avg_score', 'desc')
                ->take(5)
                ->get();

            // Distribution based on KKM (simplified for now: >90 A, >80 B, >70 C, else D)
            $allGrades = NilaiSiswa::whereIn('id_siswa', $cohortStudentIds)->pluck('nilai_akhir');
            foreach($allGrades as $g) {
                if ($g >= 90) $gradeDistribution['A']++;
                elseif ($g >= 80) $gradeDistribution['B']++;
                elseif ($g >= 70) $gradeDistribution['C']++;
                else $gradeDistribution['D']++;
            }
        }

        // --- NEW: HALL OF FAME (All-Time) ---
        // Ranking siswa terbaik dari SELURUH ANGKATAN (Global)
        // Indicator: Rata-Rata Nilai Akhir Tertinggi
        $hallOfFame = NilaiSiswa::select('id_siswa', \DB::raw('AVG(nilai_akhir) as global_avg'), \DB::raw('COUNT(id) as record_count'))
            ->groupBy('id_siswa')
            ->having('record_count', '>=', 5) // Minimal punya 5 nilai (supaya adil, siswa baru 1 nilai 100 tidak langsung juara 1)
            ->orderByDesc('global_avg')
            ->take(5)
            ->with('siswa')
            ->get()
            ->map(function($item) {
                $item->global_avg = round($item->global_avg, 2);
                // Get current class
                $cls = $item->siswa->kelas_saat_ini; 
                $item->class_name = ($cls && $cls->kelas) ? $cls->kelas->nama_kelas : 'Alumni/Lulus';
                
                // Get Year/Angkatan based on entry or current class
                // Approximation
                return $item;
            });

        // --- NEW: BINTANG PELAJAR (Active Students Only) ---
        // Ranking khusus siswa yang masih status 'aktif'
        // Bisa digunakan untuk pemilihan Siswa Tauladan / Bintang Pelajar
        $activeStars = Siswa::where('status_siswa', 'aktif')
            ->join('nilai_siswa', 'siswa.id', '=', 'nilai_siswa.id_siswa')
            ->select('siswa.id', 'siswa.nama_lengkap', \DB::raw('AVG(nilai_siswa.nilai_akhir) as avg_score'), \DB::raw('COUNT(nilai_siswa.id) as record_count'))
            ->groupBy('siswa.id', 'siswa.nama_lengkap')
            ->having('record_count', '>=', 5)
            ->orderByDesc('avg_score')
            ->take(5) // Top 5 Candidates
            ->with(['kelas_saat_ini.kelas']) // Eager load class on Siswa model directly
            ->get()
            ->map(function($item) {
                // $item is now a Siswa instance
                $item->avg_score = round($item->avg_score, 2);
                
                $cls = $item->kelas_saat_ini;
                $item->class_name = ($cls && $cls->kelas) ? $cls->kelas->nama_kelas : '-';
                return $item;
            });

        return view('tu.dashboard', compact(
            'totalSiswa', 'totalKelas', 'activeYear', 
            'chartLabels', 'chartData', 
            'availableAngkatan', 'selectedAngkatanId', 'cohortDescription',
            'topStudent', 'bestClass', 'trend',
            'hardestSubjects', 'easiestSubjects', 'gradeDistribution',
            'hallOfFame', 'activeStars'
        ));
    }

    public function dkn()
    {
        // Default to Class 6 MI (First available)
        // Ideally user selects class. For now, fetch first final year class.
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        
        // Cari kelas akhir (Kelas 6 MI or Kelas 9 MTs)
        $finalClasses = Kelas::where('id_tahun_ajaran', $activeYear->id)
            ->where(function($q) {
                $q->where('nama_kelas', 'LIKE', '%6%') // MI Class 6
                  ->orWhere('nama_kelas', 'LIKE', '%9%'); // MTs Class 9
            })->get();

        return view('tu.dkn-selector', compact('finalClasses'));
    }

    public function showDknArchive(Kelas $kelas)
    {
        // 1. Get Students
        $students = $kelas->anggota_kelas()->with('siswa')->get();
        $studentIds = $students->pluck('id_siswa');

        // 2. Fetch ALL Grades needed
        $allGrades = NilaiSiswa::whereIn('id_siswa', $studentIds)
            ->with(['mapel', 'periode', 'kelas']) 
            ->get();

        // 3. Identify Superset of Mapels
        $mapelIds = $allGrades->pluck('id_mapel')->unique();
        $mapels = Mapel::whereIn('id', $mapelIds)->orderBy('id', 'asc')->get();

        // 4. Structure Data (Archive Mode: Detailed)
        $dknData = [];

        // Fetch Saved Ijazah Grades for these students
        $ijazahGrades = \App\Models\NilaiIjazah::whereIn('id_siswa', $studentIds)->get();

        foreach ($students as $ak) {
            $sId = $ak->id_siswa;
            $sGrades = $allGrades->where('id_siswa', $sId);
            $sIjazah = $ijazahGrades->where('id_siswa', $sId);

            $studentData = [
                'student' => $ak->siswa,
                'data' => [] // Changed from 'levels' to 'data' to match View
            ];

            // Organize by Level (Tingkat) -> Periode
            for ($lvl = 1; $lvl <= 6; $lvl++) {
                $studentData['data'][$lvl] = []; // Changed to 'data'
                $lvlGrades = $sGrades->filter(fn($g) => $g->kelas && $g->kelas->tingkat_kelas == $lvl);

                // Use Integer 1, 2, 3 for Keys
                foreach ([1, 2, 3] as $p) {
                    $pName = "Cawu $p"; // Search term
                    // Fallback for Semesters if Cawu not found?
                    // Ideally we check if period->nama_periode contains number $p.
                    
                    $pGrades = $lvlGrades->filter(function($g) use ($p) {
                        if (!$g->periode) return false;
                        // Robust check: contains "Cawu $p" OR "Semester $p" OR just ends with " $p"?
                        // Let's allow loose matching for number
                        return stripos($g->periode->nama_periode, (string)$p) !== false; 
                    });
                    
                    $mapelScores = [];
                    foreach ($mapels as $m) {
                        $val = $pGrades->where('id_mapel', $m->id)->sortByDesc('updated_at')->first(); // Get latest
                        $mapelScores[$m->id] = $val ? $val->nilai_akhir : null;
                    }
                    $studentData['data'][$lvl][$p] = $mapelScores;
                }
            }

            // Calculate Summary
            $jenjang = $kelas->jenjang->kode ?? ($kelas->tingkat_kelas <= 6 ? 'MI' : 'MTS');
            $summary = $this->calculateSummary($sGrades, $mapels, $sIjazah, $jenjang);
            $studentData['summary'] = $summary;

            $dknData[] = $studentData;
        }

        // Fetch Promotion Decisions for Veto Logic
        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
        $promotionDecisions = \Illuminate\Support\Facades\DB::table('promotion_decisions')
            ->where('id_kelas', $kelas->id)
            ->where('id_tahun_ajaran', $activeYear->id)
            ->select('id_siswa', 'final_decision', 'notes') // Fetch notes too
            ->get()
            ->keyBy('id_siswa');

        $school = \App\Models\IdentitasSekolah::first();
        return view('tu.dkn-archive', compact('kelas', 'mapels', 'dknData', 'school', 'promotionDecisions'));
    }

    public function showDknSimple(Kelas $kelas)
    {
        $students = $kelas->anggota_kelas()->with('siswa')->get();
        $studentIds = $students->pluck('id_siswa');

        // Fetch Grades (Optimized)
        $allGrades = NilaiSiswa::whereIn('id_siswa', $studentIds)->get();
        
        // Mapels
        $currentMapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
        $mapels = Mapel::whereIn('id', $currentMapelIds)->orderBy('id', 'asc')->get();

        // **NEW**: Fetch Saved Ijazah Grades
        $ijazahGrades = \App\Models\NilaiIjazah::whereIn('id_siswa', $studentIds)->get(); // Collection

        $dknData = [];

        foreach ($students as $ak) {
            $sGrades = $allGrades->where('id_siswa', $ak->id_siswa);
            $sIjazah = $ijazahGrades->where('id_siswa', $ak->id_siswa); // Collection for this student

            $jenjang = $kelas->jenjang->kode ?? ($kelas->tingkat_kelas <= 6 ? 'MI' : 'MTS');
            $summary = $this->calculateSummary($sGrades, $mapels, $sIjazah, $jenjang);
            
            $dknData[] = [
                'student' => $ak->siswa,
                'summary' => $summary
            ];
        }

        return view('tu.dkn-simple', compact('kelas', 'mapels', 'dknData'));
    }

    private function calculateSummary($grades, $mapels, $sIjazah, $jenjang = 'MI')
    {
        $summary = ['rr' => [], 'um' => [], 'na' => []];
        
        // Fetch Settings
        $bRapor = \App\Models\GlobalSetting::val('ijazah_bobot_rapor', 60);
        $bUjian = \App\Models\GlobalSetting::val('ijazah_bobot_ujian', 40);
        
        $rangeKey = ($jenjang === 'MTS') ? 'ijazah_range_mts' : 'ijazah_range_mi';
        $defaultRange = ($jenjang === 'MTS') ? [7,8,9] : [4,5,6];
        $rangeVal = \App\Models\GlobalSetting::val($rangeKey, implode(',', $defaultRange));
        $targetLevels = $rangeVal ? explode(',', $rangeVal) : $defaultRange;

        foreach ($mapels as $m) {
            $mapelHistory = $grades->where('id_mapel', $m->id);
            
            // Filter Range (Rata-Rata Rapor Source)
            $filteredHistory = $mapelHistory->filter(function($g) use ($targetLevels) {
                return $g->kelas && in_array($g->kelas->tingkat_kelas, $targetLevels);
            });

            $avgCalc = $filteredHistory->count() > 0 ? $filteredHistory->avg('nilai_akhir') : 0;
            
            // Check Database for Manual Override or Saved Data
            $savedRecord = $sIjazah->where('id_mapel', $m->id)->first();
            
            // Priority: Manual Input > Calculated Avg
            $rr = $savedRecord && $savedRecord->rata_rata_rapor !== null ? $savedRecord->rata_rata_rapor : round($avgCalc, 2);
            $um = $savedRecord ? $savedRecord->nilai_ujian_madrasah : null; // Default null if not set
            
            // Final Calculation (Dynamic)
            $na = null;
            if ($rr !== null && $um !== null) {
                $na = ($rr * ($bRapor/100)) + ($um * ($bUjian/100));
                $na = round($na, 2);
            } elseif ($savedRecord && $savedRecord->nilai_ijazah !== null) {
                 $na = $savedRecord->nilai_ijazah;
            }

            $summary['rr'][$m->id] = $rr;
            $summary['um'][$m->id] = $um;
            $summary['na'][$m->id] = $na;
        }
        return $summary;
    }

    public function storeNilaiIjazah(Request $request, Kelas $kelas)
    {
        // Format: grades[siswa_id][mapel_id][rr|um]
        $data = $request->grades;
        
        if ($data) {
            foreach ($data as $siswaId => $mapels) {
                foreach ($mapels as $mapelId => $scores) {
                    
                    $rr = isset($scores['rr']) && $scores['rr'] !== '' ? $scores['rr'] : null;
                    $um = isset($scores['um']) && $scores['um'] !== '' ? $scores['um'] : null;
                    
                    if ($rr !== null || $um !== null) {
                        
                        $na = null;
                        if ($rr !== null && $um !== null) {
                            $na = ($rr * 0.6) + ($um * 0.4);
                        }

                        \App\Models\NilaiIjazah::updateOrCreate(
                            ['id_siswa' => $siswaId, 'id_mapel' => $mapelId],
                            [
                                'rata_rata_rapor' => $rr,
                                'nilai_ujian_madrasah' => $um,
                                'nilai_ijazah' => $na
                            ]
                        );
                    }
                }
            }
        }
        
        return back()->with('success', 'Data Nilai Ijazah berhasil disimpan.');
    }

    public function globalMonitoring(Request $request)
    {
        // 1. Filter Context (Year & Period)
        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        $selectedYearId = $request->year_id ?? $activeYear->id;
        $activeYear = \App\Models\TahunAjaran::find($selectedYearId);
        
        $years = \App\Models\TahunAjaran::orderBy('id', 'desc')->get();
        
        // Fetch ALL periods for this year
        $periods = Periode::where('id_tahun_ajaran', $selectedYearId)->get();
        
        // Filter Logic
        $selectedPeriodId = $request->period_id; // can be 'all' or ID
        $currentPeriod = null; // null means ALL if selectedPeriodId is 'all', or default to active if empty
        
        if ($selectedPeriodId === 'all') {
            $currentPeriod = null; // Mode: All Periods
        } elseif ($selectedPeriodId) {
            $currentPeriod = $periods->find($selectedPeriodId);
        } else {
             // Default: Active period
             $currentPeriod = $periods->where('status', 'aktif')->first() ?? $periods->first();
             // If User explicitly wants "All" on load, they should use ?period_id=all. Default is specific.
        }
        
        // 2. Fetch Classes (Filtered by Jenjang & Selection)
        $classesQuery = Kelas::where('id_tahun_ajaran', $selectedYearId)
            ->with(['wali_kelas', 'jenjang'])
            ->withCount('anggota_kelas');

        if ($request->jenjang) {
            $classesQuery->whereHas('jenjang', fn($q) => $q->where('kode', $request->jenjang));
        }
        
        // If a specific class is selected
        if ($request->kelas_id) {
            $classesQuery->where('id', $request->kelas_id);
        }

        $classes = $classesQuery->orderBy('id_jenjang')
            ->orderBy('tingkat_kelas')
            ->orderBy('nama_kelas')
            ->get();
            
        // For Dropdown List (Unfiltered by class selection, but filtered by Jenjang)
        $allClasses = Kelas::where('id_tahun_ajaran', $selectedYearId);
        if ($request->jenjang) {
             $allClasses->whereHas('jenjang', fn($q) => $q->where('kode', $request->jenjang));
        }
        $allClasses = $allClasses->orderBy('nama_kelas')->get();

        // Periods (Filtered by Jenjang)
        $periodsQuery = Periode::where('id_tahun_ajaran', $selectedYearId);
        if ($request->jenjang) {
            $periodsQuery->where('lingkup_jenjang', $request->jenjang);
        }
        $periods = $periodsQuery->orderBy('nama_periode')->get(); // Replaced 'semester' with 'nama_periode' just in case
            
        // 3. Pre-calculate Period Counts per Jenjang (for 'All' mode)
        $periodCountsByJenjang = []; // ['MI' => 3, 'MTS' => 2]
        if ($selectedPeriodId === 'all') {
            // Map Jenjang Name (or ID if linked) to count
            // Assumes periode.lingkup_jenjang matches jenjang.nama/kode?
            // Or we iterate all periods.
            $miCount = $periods->where('lingkup_jenjang', 'MI')->count();
            $mtsCount = $periods->where('lingkup_jenjang', 'MTS')->count();
            // Fallback if null
            if ($miCount == 0) $miCount = $periods->where('tipe', 'CAWU')->count();
            if ($mtsCount == 0) $mtsCount = $periods->where('tipe', 'SEMESTER')->count();
            
            $periodCountsByJenjang['MI'] = $miCount > 0 ? $miCount : 1; 
            $periodCountsByJenjang['MTS'] = $mtsCount > 0 ? $mtsCount : 1;
            $periodCountsByJenjang['MA'] = $mtsCount > 0 ? $mtsCount : 1; // Alias
        }
            
        $monitoringData = [];
        
        if ($classes->count() > 0) {
            // Assignments (Mapel Count per Class)
            // Note: assignments are per year typically.
            $assignments = \App\Models\PengajarMapel::whereIn('id_kelas', $classes->pluck('id'))
                ->selectRaw('id_kelas, count(*) as mapel_count')
                ->groupBy('id_kelas')
                ->pluck('mapel_count', 'id_kelas');
                
            // Actual Grades Count
            $gradesQuery = NilaiSiswa::whereIn('id_kelas', $classes->pluck('id'));
            
            if ($currentPeriod) {
                $gradesQuery->where('id_periode', $currentPeriod->id);
            } else {
                // All Periods: Filter by Year via Class/Period linking
                // NilaiSiswa has id_periode. We want periods in THIS year.
                $gradesQuery->whereIn('id_periode', $periods->pluck('id'));
            }
            
            $gradesCount = $gradesQuery->selectRaw('id_kelas, count(*) as total_grades')
                ->groupBy('id_kelas')
                ->pluck('total_grades', 'id_kelas');
                
            foreach ($classes as $cls) {
                $studentCount = $cls->anggota_kelas_count;
                $mapelCount = $assignments[$cls->id] ?? 0;
                
                // Determine Multiplier
                $periodMultiplier = 1;
                if ($selectedPeriodId === 'all') {
                    // Match Jenjang
                    $jName = strtoupper($cls->jenjang->nama ?? ''); // MI, MTS, MA
                    if (str_contains($jName, 'MI')) $periodMultiplier = $periodCountsByJenjang['MI'] ?? 1;
                    else if (str_contains($jName, 'MTS') || str_contains($jName, 'MA')) $periodMultiplier = $periodCountsByJenjang['MTS'] ?? 1;
                }
                
                $expected = $studentCount * $mapelCount * $periodMultiplier;
                $actual = $gradesCount[$cls->id] ?? 0;
                
                $progress = ($expected > 0) ? round(($actual / $expected) * 100) : 0;
                if ($progress > 100) $progress = 100; 
                
                // Status
                $status = 'Belum Mulai';
                $color = 'slate';
                
                if ($progress == 100) {
                    $status = 'Selesai';
                    $color = 'emerald';
                } elseif ($progress > 50) {
                     $status = 'Proses ' . $progress . '%';
                     $color = 'blue';
                } elseif ($progress > 0) {
                     $status = 'Proses ' . $progress . '%';
                     $color = 'amber';
                }
                
                // Add Period Label Info for UI
                $periodLabel = '';
                if ($selectedPeriodId === 'all') {
                    $periodLabel = (str_contains(strtoupper($cls->jenjang->nama ?? ''), 'MI')) ? 'Semua Cawu' : 'Semua Semester';
                } else {
                    $periodLabel = $currentPeriod->nama_periode ?? '-';
                }
                
                $monitoringData[] = (object) [
                    'class' => $cls,
                    'progress' => $progress,
                    'status' => $status,
                    'color' => $color,
                    'mapel_count' => $mapelCount,
                    'student_count' => $studentCount,
                    'period_label' => $periodLabel // New data for UI
                ];
            }
        }

        return view('tu.monitoring_global', compact('years', 'periods', 'currentPeriod', 'activeYear', 'monitoringData', 'selectedPeriodId', 'allClasses'));
    }

    public function downloadDknExcel(Kelas $kelas)
    {
        $school = \App\Models\IdentitasSekolah::first();
        
        // Data Gathering
        $students = $kelas->anggota_kelas()->with('siswa')->get();
        $studentIds = $students->pluck('id_siswa');
        
        // Fetch ALL Grades for Archive
        $allGrades = NilaiSiswa::whereIn('id_siswa', $studentIds)
            ->with(['mapel', 'periode', 'kelas']) 
            ->get();

        $mapelIds = $allGrades->pluck('id_mapel')->unique();
        $mapels = Mapel::whereIn('id', $mapelIds)->orderBy('id', 'asc')->get();
        $ijazahGrades = \App\Models\NilaiIjazah::whereIn('id_siswa', $studentIds)->get();
        
        $jenjang = $kelas->jenjang->kode ?? ($kelas->tingkat_kelas <= 6 ? 'MI' : 'MTS');
        
        // Spreadsheet Setup
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header Info
        $sheet->setCellValue('A1', 'DAFTAR KUMPULAN NILAI (DKN) IJAZAH');
        $sheet->setCellValue('A2', strtoupper($school->nama_sekolah ?? 'SEKOLAH'));
        $sheet->setCellValue('A3', 'Kelas: ' . $kelas->nama_kelas . ' | Tahun: ' . ($kelas->active_year_name ?? date('Y')));
        
        // Merge logic moved to after column calculation

        // Table Header
        $row = 5;
        $sheet->setCellValue('A'.$row, 'NO');
        $sheet->setCellValue('B'.$row, 'NAMA SISWA');
        $sheet->setCellValue('C'.$row, 'KELAS / CAWU'); // Col C width needs adjustment
        
        $col = 4; // D
        foreach($mapels as $mapel) {
            $colStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($colStr.$row, $mapel->nama_mapel); // Top Header
            // We can add MP code in next row if we want double header, but single is fine for now based on image
            // Image has MP1, MP2... below. Let's try simple single header first or standard.
            // User: "seperti ini" image has merged cells for Mapels (Name then Code). 
            // We'll stick to Name for clarity in one row to save vertical space.
            $col++;
        }
        $colStrAvg = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->setCellValue($colStrAvg.$row, 'RATA-RATA');
        $col++;
        $colStrKet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->setCellValue($colStrKet.$row, 'KETERANGAN');
        
        // Header Layout
        $lastCol = $colStrKet;
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(25);
        
        $headerRange = "A$row:$lastCol$row";
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // --- FIXED HEADER MERGE & AUTOFIT ---
        // Merge Title Rows 1-3 now that we know width
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->getStyle("A1:{$lastCol}3")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastCol}3")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Autofit Columns
        // A -> No, B -> Name, C -> Kelas/Cawu ... to LastCol
        $sheet->getColumnDimension('A')->setAutoSize(true);
        // B (Name) is set to fixed 25, keep it or auto? User said "auto fit aja".
        // Often Names need width limits or they get huge. Let's try AutoSize but maybe min/max? 
        // Simple AutoSize for standard columns.
        $sheet->getColumnDimension('B')->setAutoSize(true);
        // C is fixed 15? Let's Auto as requested.
        $sheet->getColumnDimension('C')->setAutoSize(true);
        
        $currColIndex = 4; // D onwards
        $colLimit = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
        for ($i = 4; $i <= $colLimit; $i++) {
            $colStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colStr)->setAutoSize(true);
        }
        
        $row++; // Start Data
        $no = 1;
        $minLulus = \App\Models\GlobalSetting::val('ijazah_min_lulus', 60);

        foreach($students as $ak) {
            $startRow = $row;
            
            // Prepare Student Data
            $sId = $ak->id_siswa;
            $sGrades = $allGrades->where('id_siswa', $sId);
            $sIjazah = $ijazahGrades->where('id_siswa', $sId);
            
            // Loop Levels 1-6 (Standard for MI)
            for ($lvl = 1; $lvl <= 6; $lvl++) {
                $lvlGrades = $sGrades->filter(fn($g) => $g->kelas && $g->kelas->tingkat_kelas == $lvl);
                
                foreach (['Cawu 1', 'Cawu 2', 'Cawu 3'] as $pName) {
                    // Filter grades for this Period
                    $pGrades = $lvlGrades->filter(fn($g) => $g->periode && stripos($g->periode->nama_periode, $pName) !== false);
                    
                    // Row Label
                    $sheet->setCellValue('C'.$row, "Kls $lvl | " . substr($pName, -1));
                    
                    // Mapel Scores
                    $currCol = 4;
                    $rowSum = 0; $rowCount = 0;
                    
                    foreach ($mapels as $m) {
                        $val = $pGrades->where('id_mapel', $m->id)->first();
                        $score = $val ? $val->nilai_akhir : null;
                        
                        $cStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol);
                        // Raw Scores: Integer (No decimals)
                        $valStr = $score !== null ? round($score) : '-';
                        $sheet->setCellValue($cStr.$row, $valStr);
                        if ($valStr === '-') {
                             $sheet->getStyle($cStr.$row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE'); // Gray
                        }
                        
                        if($score !== null) { $rowSum += $score; $rowCount++; }
                        $currCol++;
                    }
                    
                    // Row Average: 2 Decimals (Force '0.00' format)
                    $rowAvg = $rowCount > 0 ? $rowSum / $rowCount : 0;
                    $avgVal = $rowCount > 0 ? $rowAvg : '-';
                    $sheet->setCellValue($colStrAvg.$row, $avgVal);
                    if ($avgVal === '-') {
                        $sheet->getStyle($colStrAvg.$row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
                    }
                    if($rowCount > 0) {
                        $sheet->getStyle($colStrAvg.$row)->getNumberFormat()->setFormatCode('0.00');
                    }
                    
                    $row++;
                }
            }
            
            // Summaries
            $summary = $this->calculateSummary($sGrades, $mapels, $sIjazah, $jenjang);
            
            // RR Row
            $sheet->setCellValue('C'.$row, 'Rata-Rapor (RR)');
            $sheet->getStyle('C'.$row)->getFont()->setBold(true);
            $sheet->getStyle("A$row:$colStrKet$row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF0E0'); // Yellowish
            
            $currCol = 4;
            foreach ($mapels as $m) {
                $cStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol);
                $val = $summary['rr'][$m->id] ?? 0;
                $valStr = $val != 0 ? $val : '-';
                $sheet->setCellValue($cStr.$row, $valStr);
                if ($valStr === '-') {
                    $sheet->getStyle($cStr.$row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
                }
                if($val != 0) $sheet->getStyle($cStr.$row)->getNumberFormat()->setFormatCode('0.00');
                $currCol++;
            }
            $row++;
            
            // UM Row
            $sheet->setCellValue('C'.$row, 'Ujian Mdr (UM)');
            $sheet->getStyle('C'.$row)->getFont()->setBold(true);
            $sheet->getStyle("A$row:$colStrKet$row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFF6FF'); // Blueish
            
            $currCol = 4;
            foreach ($mapels as $m) {
                $cStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol);
                $val = $summary['um'][$m->id] ?? 0;
                $valStr = $val != 0 ? $val : '-';
                $sheet->setCellValue($cStr.$row, $valStr);
                if ($valStr === '-') {
                    $sheet->getStyle($cStr.$row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
                }
                if($val != 0) $sheet->getStyle($cStr.$row)->getNumberFormat()->setFormatCode('0.00');
                $currCol++;
            }
            $row++;
            
            // NA Row
            $sheet->setCellValue('C'.$row, 'Nilai Akhir (NA)');
            $sheet->getStyle('C'.$row)->getFont()->setBold(true);
            $sheet->getStyle("A$row:$colStrKet$row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCFCE7'); // Greenish
            $sheet->getStyle("A$row:$colStrKet$row")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

            $naValues = [];
            $currCol = 4;
            foreach ($mapels as $m) {
                $cStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol);
                $val = $summary['na'][$m->id] ?? 0;
                $valStr = $val != 0 ? $val : '-';
                $sheet->setCellValue($cStr.$row, $valStr);
                if ($valStr === '-') {
                     $sheet->getStyle($cStr.$row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
                }
                if($val != 0) {
                     $sheet->getStyle($cStr.$row)->getNumberFormat()->setFormatCode('0.00');
                     $naValues[] = $val;
                }
                $sheet->getStyle($cStr.$row)->getFont()->setBold(true);
                $currCol++;
            }
            
            // Calculate Status Logic (With Veto)
            $naAvg = count($naValues) > 0 ? array_sum($naValues) / count($naValues) : 0;
            
            // 1. Academic Check
            $academicPass = $naAvg >= $minLulus;
            
            // 2. Veto Check (Promotion Decision)
            // Fetch decision for this student in this year
            $promoRecord = \Illuminate\Support\Facades\DB::table('promotion_decisions')
                ->where('id_siswa', $ak->id_siswa)
                ->where('id_kelas', $kelas->id)
                ->first();
                
            $promoDecision = $promoRecord->final_decision ?? null;
            $promoNote = $promoRecord->notes ?? '';

            // Logic: If explicitly retained/not_graduated, FAIL.
            if (in_array($promoDecision, ['retained', 'not_graduated'])) {
                $status = 'Tidak Lulus';
            } elseif ($academicPass) {
                $status = 'Lulus';
            } else {
                $status = 'Tidak Lulus';
            }
            
            // Merge Columns for Student Info
            $endRow = $row; // Current row is NA row
            
            $sheet->setCellValue('A'.$startRow, $no++);
            $sheet->mergeCells("A$startRow:A$endRow");
            $sheet->getStyle("A$startRow")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            
            $nisVal = $ak->siswa->nis_lokal ?? $ak->siswa->nis ?? $ak->siswa->nisn ?? '-';
            $sheet->setCellValue('B'.$startRow, $ak->siswa->nama_lengkap . "\nNIS: " . $nisVal);
            $sheet->getStyle('B'.$startRow)->getAlignment()->setWrapText(true);
            $sheet->mergeCells("B$startRow:B$endRow");
            $sheet->getStyle("B$startRow")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            
            // Status Column Merge
            // Append Note if Failed
            $statusText = $status;
            if ($status === 'Tidak Lulus' && !empty($promoNote)) {
                $statusText .= "\n(" . $promoNote . ")";
            }

            $sheet->setCellValue($colStrKet.$startRow, $statusText);
            $sheet->mergeCells("$colStrKet$startRow:$colStrKet$endRow");
            $sheet->getStyle("$colStrKet$startRow")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("$colStrKet$startRow")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle("$colStrKet$startRow")->getAlignment()->setWrapText(true); // Allow wrap for note
            $sheet->getStyle("$colStrKet$startRow")->getFont()->setBold(true);
            $sheet->getStyle("$colStrKet$startRow")->getFont()->setColor(
                $status === 'Lulus' 
                ? new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKGREEN) 
                : new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED)
            );

            $row++; // Next Student
        }
        
        // Borders All
        $lastRow = $row - 1;
        $sheet->getStyle("A5:$lastCol$lastRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Alignment: Middle Center for ALL Data
        $sheet->getStyle("A5:$lastCol$lastRow")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A5:$lastCol$lastRow")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        
        // --- ADD LEGEND & SIGNATURE (User Request) ---
        $row += 2;
        $sheet->setCellValue('A'.$row, 'Keterangan:');
        $sheet->getStyle('A'.$row)->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue('A'.$row, '1. Rata-Rapor (RR) diambil dari Rata-rata Nilai Rapor semester/kelas yang ditentukan.');
        $row++;
        $sheet->setCellValue('A'.$row, '2. Rumus Nilai Akhir: NA = (Rapor × 60%) + (Ujian × 40%).');
        $row++;
        $sheet->setCellValue('A'.$row, '3. Kriteria Kelulusan: Rata-rata Nilai Akhir minimal 60.00.');
        
        // Signature Block (Right Aligned)
        $lastColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
        $sigStartColIndex = max(1, $lastColIndex - 6); 
        $sigCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($sigStartColIndex);
        
        $row += 2; 
        $dDate = date('d F Y');
        $city = $school->kabupaten ?? 'Kabupaten';
        
        $sheet->setCellValue($sigCol.$row, "$city, $dDate");
        $sheet->mergeCells("$sigCol$row:$lastCol$row");
        $sheet->getStyle("$sigCol$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $row++;
        $sheet->setCellValue($sigCol.$row, 'Kepala Madrasah,');
        $sheet->mergeCells("$sigCol$row:$lastCol$row");
        $sheet->getStyle("$sigCol$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $row += 4;
        $hmName = $school->kepala_madrasah ?? '......................';
        $sheet->setCellValue($sigCol.$row, $hmName);
        $sheet->mergeCells("$sigCol$row:$lastCol$row");
        $sheet->getStyle("$sigCol$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("$sigCol$row")->getFont()->setBold(true)->setUnderline(true);
        
        $row++;
        $hmNip = $school->nip_kepala ?? '-';
        $sheet->setCellValue($sigCol.$row, "NIP. $hmNip");
        $sheet->mergeCells("$sigCol$row:$lastCol$row");
        $sheet->getStyle("$sigCol$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);        
        // --- PRINTING SETUP (Make it ready to print without editing) ---
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0); // Infinite height
        
        // Margins (Narrow)
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.5);
        $sheet->getPageMargins()->setLeft(0.5);
        $sheet->getPageMargins()->setBottom(0.5);
        
        // Repeat Header Rows (1 to 5)
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);
        
        // Output
        $fileName = 'DKN_Archive_' . preg_replace('/[^A-Za-z0-9]/', '_', $kelas->nama_kelas) . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName) .'"');
        $writer->save('php://output');
        exit;
    }
}
