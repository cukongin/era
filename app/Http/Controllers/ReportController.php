<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\NilaiSiswa;
use App\Models\PengajarMapel;
use Illuminate\Support\Facades\DB;

use App\Models\ReportTemplate;
use App\Models\Mapel;
use App\Models\KkmMapel;
use App\Models\IdentitasSekolah;
use App\Models\GradingFormula; // Ensure Import
use App\Services\FormulaEngine; // Ensure Import

class ReportController extends Controller
{
    public function leger(Request $request)
    { 
        $user = auth()->user();

        // 1. Determine Year Context (Same as Index)
        $years = [];
        $selectedYear = null;

        if ($user->role == 'admin' || $user->id == 1 || $user->isStaffTu()) {
            $years = TahunAjaran::orderBy('nama', 'desc')->get();
            if ($request->year_id) {
                $selectedYear = TahunAjaran::find($request->year_id);
            }
        }
        
        if (!$selectedYear) {
            $selectedYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        }

        $classes = collect([]); // Default collection
        $selectedClass = null;
        $students = collect([]);
        $mapels = collect([]);
        $grades = collect([]);
        $kkm = collect([]);
        $kkm = collect([]);
        $periode = null;
        $periodes = collect([]); 

        // Determine Access
        if ($user->role == 'admin' || $user->id == 1 || $user->isStaffTu()) {
            // Jenjang Filter
            $selectedJenjang = $request->jenjang ?? 'MI'; // Default to MI

            $classes = Kelas::where('id_tahun_ajaran', $selectedYear->id)
                ->whereHas('jenjang', function($q) use ($selectedJenjang) {
                    $q->where('kode', $selectedJenjang);
                })
                ->orderBy('nama_kelas')
                ->get();
        }

        // Logic: Auto-select items if only one exists
        $classId = $request->class_id;
        if ($classes->count() == 1 && !$classId) {
             $classId = $classes->first()->id;
        }

        // 2. If Class Selected, Fetch Leger Data (Adapted from WaliKelasController)
        if ($classId) {
            $selectedClass = Kelas::with(['wali_kelas', 'jenjang', 'tahun_ajaran'])->where('id', $classId)->first();
        
        \Log::info("Leger Debug: Class ID {$request->class_id} found: " . ($selectedClass ? 'Yes' : 'No'));
        if ($selectedClass) {
             \Log::info("Leger Debug: User Role: {$user->role}, ID: {$user->id}, Allowed: " . (($user->role == 'admin' || $user->id == 1 || $user->isStaffTu()) ? 'Yes' : 'No'));
        }

        // Security Check: User must be allowed to see this class
        // Start with strict check, but Admin/TU generally have global access
        if ($selectedClass && ($user->role == 'admin' || $user->id == 1 || $user->isStaffTu())) {
             
             // Get Period (Default to Ganjil/Genap relevant to Year?)
             // Usually Leger is per semester. 
             // We need to know WHICH period to show. 
             // Default to the first period of that year OR the active one if it matches year?
             // Let's try to find periods for this year.
             // Logic: If Class is selected, its Jenjang MUST be the source of truth for Periods.
         // $selectedJenjang is only for the dropdown filter.
         $jenjangKode = $selectedClass ? $selectedClass->jenjang->kode : ($selectedJenjang ?? 'MI'); 
         
         // \Log::info("Leger Debug: Jenjang Kode Used: $jenjangKode");
         
         $periodes = Periode::where('id_tahun_ajaran', $selectedYear->id)
             ->where('lingkup_jenjang', $jenjangKode) // Filter by scope
             ->get();
             
             \Log::info("Leger Debug: Periods Count: " . $periodes->count());

             // If request has period_id, use it. Else default to active or first.
             if ($request->period_id) {
                 $periode = $periodes->firstWhere('id', $request->period_id);
             }
             
             if (!$periode) {
                 // Try to find active
                 $periode = $periodes->firstWhere('status', 'aktif');
                 // If active period is NOT in this year (historical viewing), default to last period of that year (Genap usually) or first?
                 if (!$periode) $periode = $periodes->last(); // Viewing historical year -> likely want final results? or Allow Selector?
             }
             
             \Log::info("Leger Debug: Selected Periode: " . ($periode ? $periode->id : 'None'));


                 if ($selectedClass && $periode) {
                    $students = $selectedClass->anggota_kelas()->with('siswa')->get();
                    
                    // Fetch Mapels
                    $assignedMapelIds = \App\Models\PengajarMapel::where('id_kelas', $selectedClass->id)
                        ->pluck('id_mapel');
                        
                    $mapels = Mapel::whereIn('id', $assignedMapelIds)
                        ->orderBy('kategori', 'asc')
                        ->orderBy('nama_mapel', 'asc')
                        ->get();
                    
                    // Fetch Grades
                    $grades = NilaiSiswa::where('id_kelas', $selectedClass->id)
                        ->where('id_periode', $periode->id)
                        ->get()
                        ->keyBy(fn($item) => $item->id_siswa . '-' . $item->id_mapel);

                    // Fetch KKM
                    $kkm = KkmMapel::where('id_tahun_ajaran', $selectedYear->id)
                         ->where('jenjang_target', $selectedClass->jenjang->kode) 
                         ->pluck('nilai_kkm', 'id_mapel');
                 }
            }
        }

        $showOriginal = $request->boolean('show_original');

        return view('reports.leger', compact('years', 'selectedYear', 'classes', 'selectedClass', 'students', 'mapels', 'grades', 'kkm', 'periode', 'periodes', 'selectedJenjang', 'showOriginal'));
    }

    public function exportLeger(Request $request) 
    {
        $user = auth()->user();
        if (!($user->role == 'admin' || $user->id == 1 || $user->isStaffTu())) {
            return back()->with('error', 'Akses ditolak.');
        }

        $yearId = $request->year_id;
        $classId = $request->class_id;

        if (!$yearId || !$classId) {
            return back()->with('error', 'Harap pilih Tahun dan Kelas terlebih dahulu.');
        }

        $selectedYear = TahunAjaran::findOrFail($yearId);
        $kelas = Kelas::with(['wali_kelas', 'jenjang', 'tahun_ajaran'])->findOrFail($classId);

        // Access Check
        // (Simplified check already done above via role)

        // Determine Period (Same logic as leger)
        $periodes = Periode::where('id_tahun_ajaran', $selectedYear->id)->get();
        // Prefer request period, then active, then last
        if ($request->period_id) {
            $periode = $periodes->firstWhere('id', $request->period_id);
        } else {
            $periode = $periodes->firstWhere('status', 'aktif');
            if (!$periode) $periode = $periodes->last();
        }

        if (!$periode) return back()->with('error', 'Periode tidak ditemukan.');

        $showOriginal = $request->boolean('show_original');

        $students = $kelas->anggota_kelas()->with('siswa')->get();
        
        // Mapels
        $assignedMapelIds = PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
        $mapels = Mapel::whereIn('id', $assignedMapelIds)
            ->orderBy('kategori', 'asc')
            ->orderBy('nama_mapel', 'asc')
            ->get();
            
        // Grades
        // Grades
        $allRawGrades = NilaiSiswa::where('id_kelas', $kelas->id)
            ->where('id_periode', $periode->id)
            ->get();

        // [MOD] Apply Custom Formula (If Active, and NOT showing original)
        if (!$showOriginal) {
            $jenjang = strtolower($kelas->jenjang->kode ?? 'mi');
            $context = ($jenjang === 'mts') ? 'rapor_mts' : 'rapor_mi';
            $activeFormula = GradingFormula::where('context', $context)->where('is_active', true)->first();

            if ($activeFormula) {
                foreach ($allRawGrades as $grade) {
                    $vars = [
                        '[Rata_PH]' => $grade->rata_ph ?? 0,
                        '[Nilai_PTS]' => $grade->nilai_pts ?? 0,
                        '[Nilai_PAS]' => $grade->nilai_pas ?? 0,
                        '[Nilai_Sem_1]' => 0, 
                        '[Nilai_Sem_2]' => 0,
                    ];
                    $newScore = FormulaEngine::calculate($activeFormula->formula, $vars);
                    if ($newScore > 0) {
                        $grade->nilai_akhir = round($newScore);
                    }
                }
            }
        }

        $grades = $allRawGrades->groupBy('id_siswa');
            
        // KKM
        $kkm = KkmMapel::where('id_tahun_ajaran', $selectedYear->id)
             ->where('jenjang_target', $kelas->jenjang->kode) 
             ->pluck('nilai_kkm', 'id_mapel');

        // Calculate Stats for Ranking
        $studentStats = [];
        foreach($students as $student) {
            $sGrades = $grades[$student->id_siswa] ?? collect([]);
            
            // Calculate Total based on SHOW ORIGINAL preference
            $total = 0;
            $count = 0;
            
            if ($showOriginal) {
                // Re-calculate total from Original Grades
                foreach ($mapels as $m) {
                    $g = $sGrades->where('id_mapel', $m->id)->first();
                    if ($g) {
                        $val = $g->nilai_akhir_asli ?? $g->nilai_akhir;
                        $total += $val;
                        $count++;
                    }
                }
            } else {
                // Use Final Grades (Default)
                $total = $sGrades->sum('nilai_akhir');
                $count = $sGrades->count();
            }

            $studentStats[$student->id_siswa] = [
                'total' => $total,
                'avg' => $count > 0 ? $total / $count : 0,
                'rank' => 0
            ];
        }

        // Apply Ranking
        $statsCollection = collect($studentStats)->sortByDesc('avg');
        $rank = 1;
        foreach ($statsCollection as $sid => $stat) {
             $studentStats[$sid]['rank'] = $rank++;
        }

        $filename = "Leger_" . ($showOriginal ? "MURNI_" : "") . "{$kelas->nama_kelas}_{$selectedYear->nama}_{$periode->nama_periode}.xls";
    
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        
        return view('wali-kelas.leger-export', compact('kelas', 'periode', 'students', 'mapels', 'grades', 'studentStats', 'kkm', 'showOriginal'));
    }

    public function studentAnalytics(Request $request, $studentId)
    {
        $user = auth()->user();
        $student = Siswa::with('kelas_saat_ini.kelas')->findOrFail($studentId);
        
        // 1. Determine Access & Context (Class, Period)
        // Similar logic to Leger/Rapor
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // Find class for this student in Active Year
        $enrollment = $student->anggota_kelas()
            ->whereHas('kelas', function($q) use ($activeYear) {
                $q->where('id_tahun_ajaran', $activeYear->id);
            })->first();
            
        if (!$enrollment) {
             return back()->with('error', 'Siswa tidak memiliki kelas aktif tahun ini.');
        }
        
        $kelas = $enrollment->kelas;
        
        // Find Active Period for this Class Level (MI/MTS)
        $periode = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('status', 'aktif')
            ->where('lingkup_jenjang', $kelas->jenjang->kode)
            ->first();
            
        if (!$periode) {
            // Fallback: Last period if none active
             $periode = Periode::where('id_tahun_ajaran', $activeYear->id)
                ->where('lingkup_jenjang', $kelas->jenjang->kode)
                ->latest()
                ->firstOrFail();
        }

        // 2. Data for Comparison Chart (Current Period)
        // Get Grades for this student/period
        $grades = NilaiSiswa::where('id_siswa', $student->id)
            ->where('id_periode', $periode->id)
            ->with('mapel')
            ->get();
            
        $mapelNames = [];
        $finalGrades = [];
        $originalGrades = [];
        
        foreach($grades as $g) {
            $mapelNames[] = $g->mapel->nama_mapel;
            $finalGrades[] = $g->nilai_akhir;
            $originalGrades[] = $g->nilai_akhir_asli ?? $g->nilai_akhir;
        }

        // 3. Data for Historical Trend (GPA per Period)
        // Get ALL grades for this student from all periods, grouped by period
        $allGrades = NilaiSiswa::where('id_siswa', $student->id)
            ->with(['periode', 'periode.tahun_ajaran'])
            ->get()
            ->groupBy('id_periode');
            
        $periodNames = [];
        $gpaTrends = [];
        
        // Sort periods by ID (Chronological usually) or Year
        // We need to fetch period details to sort them properly
        $sortedPeriodIds = $allGrades->keys()->sort(); // Simple sort by ID for now
        
        foreach($sortedPeriodIds as $pid) {
            $pGrades = $allGrades[$pid];
            $pModel = $pGrades->first()->periode;
            
            $periodNames[] = $pModel->tahun_ajaran->nama . ' - ' . $pModel->nama_periode;
            $avg = $pGrades->avg('nilai_akhir');
            $gpaTrends[] = round($avg, 2);
        }

        return view('reports.student_analytics', compact(
            'student', 'kelas', 'periode', 
            'mapelNames', 'finalGrades', 'originalGrades',
            'periodNames', 'gpaTrends'
        ));
    }
    public function exportLegerRekap(Request $request)
    {
        $user = auth()->user();
        if (!($user->role == 'admin' || $user->id == 1 || $user->isStaffTu())) {
            return back()->with('error', 'Akses ditolak.');
        }

        $yearId = $request->year_id;
        $classId = $request->class_id;

        if (!$yearId || !$classId) {
            return back()->with('error', 'Harap pilih Tahun dan Kelas terlebih dahulu.');
        }

        $selectedYear = TahunAjaran::findOrFail($yearId);
        $kelas = Kelas::with(['wali_kelas', 'jenjang', 'tahun_ajaran'])->findOrFail($classId);

        $students = $kelas->anggota_kelas()->with('siswa')->get();
        
        // Mapels
        $assignedMapelIds = PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
        $mapels = Mapel::whereIn('id', $assignedMapelIds)
            ->orderBy('kategori', 'asc')
            ->orderBy('nama_mapel', 'asc')
            ->get();

        // Periods (All periods in this Year and Jenjang/Level)
        // Note: WaliKelas uses $kelas->jenjang->kode for scope.
        $periods = Periode::where('id_tahun_ajaran', $selectedYear->id)
            ->where('lingkup_jenjang', $kelas->jenjang->kode)
            ->get()
            ->keyBy('id');

        // Grades (From ALL periods in this year)
        $grades = NilaiSiswa::where('id_kelas', $kelas->id)
            ->whereIn('id_periode', $periods->keys())
            ->get()
            ->groupBy('id_siswa'); 
            
        // KKM
        $kkm = KkmMapel::where('id_tahun_ajaran', $selectedYear->id)
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
             $annualTotal = array_sum($mapelAvgs); 
             
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

        $filename = "Leger_Tahunan_{$kelas->nama_kelas}_{$selectedYear->nama}.xls";
        
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        return view('wali-kelas.leger-rekap-export', compact('kelas', 'periods', 'students', 'mapels', 'grades', 'kkm', 'studentStats'));
    }
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // 1. Determine Year Context
        $years = [];
        $selectedYear = null;

        // Allow Admin/Staff TU to see allow years
        if ($user->role == 'admin' || $user->id == 1 || $user->isStaffTu()) {
            $years = TahunAjaran::orderBy('nama', 'desc')->get();
            
            if ($request->year_id) {
                $selectedYear = TahunAjaran::find($request->year_id);
            }
        }
        
        // Default to Active Year if not selected or restricted
        if (!$selectedYear) {
            $selectedYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        }

        $classes = [];
        $selectedClass = null;
        $students = collect([]);

        // Determine Access based on Selected Year
        if ($user->role == 'admin' || $user->id == 1 || $user->isStaffTu()) {
            $classes = Kelas::where('id_tahun_ajaran', $selectedYear->id)
                ->orderBy('nama_kelas')
                ->get();
        } else {
            // Wali Kelas (Restricted to Active Year usually, or their historical classes?)
            // User requested TU access primarily. Let's keep Wali restricted to Active or their assigned classes in that year.
            // Safe bet: Wali sees classes where they are wali in the SELECTED year (if we allow them to change year too? 
            // For now, prompt implies TU needs this. Let's stick to TU/Admin power.)
            // Actually, let's allow Wali to see history too if they were wali back then?
            // "kasih TU menu cetak rapor semua Tahun Ajaran" -> User specified TU.
            
            // If Wali, keep default behavior (Active Year) for now unless requested otherwise, 
            // OR allow them to see history if we pass $years to view.
            // Let's keep strict for Wali for now to match exactly "User Request: kasih TU...".
            
            $classes = Kelas::where('id_wali_kelas', $user->id)
                ->where('id_tahun_ajaran', $selectedYear->id) // If we force active, this matches.
                ->get();
        }

        // Handle Class Selection
        if ($request->class_id) {
            $selectedClass = Kelas::find($request->class_id);
        } elseif ($classes->count() > 0) {
            $selectedClass = $classes->first();
        }

        if ($selectedClass) {
            $students = $selectedClass->anggota_kelas()->with('siswa')->get()->sortBy('siswa.nama_lengkap');
        }

        return view('reports.index', compact('classes', 'selectedClass', 'students', 'years', 'selectedYear'));
    }

    public function printRapor(Request $request, $studentId)
    {
        // 1. Get Student
        $student = Siswa::with(['anggota_kelas.kelas.wali_kelas', 'anggota_kelas.kelas.tahun_ajaran', 'anggota_kelas.kelas.jenjang'])
            ->findOrFail($studentId);

        // 2. Determine Year (Active or Requested)
        if ($request->has('year_id')) {
            $year = TahunAjaran::findOrFail($request->year_id);
        } else {
            $year = TahunAjaran::where('status', 'aktif')->firstOrFail();
        }
        
        // 3. Determine Active/Historical Class Member
        $school = IdentitasSekolah::first();
        
        $classMember = $student->anggota_kelas->filter(function($ak) use ($year) {
            return $ak->kelas->id_tahun_ajaran == $year->id;
        })->first();

        // Fallback or Error
        if (!$classMember) {
            // Try to find ANY class if we defaulted to active but student has no active class (maybe alumni)
            // But if specific year requested, we should probably fail or warn.
            return back()->with('error', 'Data kelas siswa tidak ditemukan untuk tahun ajaran ' . $year->nama);
        }

        $class = $classMember->kelas;
        
        // GATHER ALL DATA USING HELPER
        // Rename $activeYear to $year in helper if needed, or just pass $year
        $data = $this->gatherReportData($student, $class, $year);
        extract($data); // Unpack variables

        // Fix variable naming for view usage (activeYear -> year)
        $activeYear = $year; 

        // CHECK FOR ACTIVE TEMPLATE (NEW)
        // Only if "Use Custom Template" setting is ON

        if (\App\Models\GlobalSetting::val('rapor_use_custom_template', 0)) {
            $template = ReportTemplate::getActive('rapor');
            if ($template) {
                 // 1. Render Tables to Strings
                 $dataForPartials = compact(
                    'student', 'class', 'activeYear', 'allPeriods', 'activePeriod', 'periodSlots',
                    'mapelGroups', 'cumulativeGrades', 'attendance', 'remarks', 'ekskuls', 'school',
                    'stats', 'totalStudents', 'kkmMapels', 'globalKkm', 'statusNaik', 'decisionText', 'periodLabel'
                 );

                 $htmlNilai = view('reports.partials.academic_table', $dataForPartials)->render();
                 $htmlEkskul = view('reports.partials.ekskul_table', $dataForPartials)->render();
                 // Only render Prestasi if allowed or placeholder present? Let's just render it.
                 // If setting hidden, the partial logic (which we should add) handles it, OR we handle it here.
                 // The partials I created didn't check the GlobalSetting, but the user can remove the placeholder [[TABEL_PRESTASI]].
                 $htmlPrestasi = view('reports.partials.prestasi_table', $dataForPartials)->render();
                 $htmlKepribadian = view('reports.partials.personality_table', $dataForPartials)->render();
                 $htmlAbsensi = view('reports.partials.attendance_table', $dataForPartials)->render();

                 // 2. Prepare Variables
                 // Dates
                 setlocale(LC_TIME, 'id_ID');
                 $jenjangKey = strtolower($class->jenjang->kode ?? 'mi');
                 $titimangsaSetting = \App\Models\GlobalSetting::val('titimangsa_' . $jenjangKey);
                 $titimangsaTempat = \App\Models\GlobalSetting::val('titimangsa_tempat_' . $jenjangKey);
                 
                 $tglRapor = $titimangsaSetting ?: \Carbon\Carbon::now()->formatLocalized('%d %B %Y'); 
                 $tempatRapor = $titimangsaTempat ?: ($school->kabupaten ?? $school->kota ?? 'Tempat');

                 // Wali Note
                 $latestRem = $remarks->last(); 
                 $note = $latestRem ? $latestRem->catatan_akademik : '-';

                 // Extract Stats for Active Period
                 $myStats = $stats[$activePeriod->id] ?? ['total'=>0, 'average'=>0, 'rank'=>'-', 'count'=>0];

                 $vars = [
                    '[[NAMA_SISWA]]' => $student->nama_lengkap ?? $student->nama,
                    '[[NIS]]' => $student->nis,
                    '[[NISN]]' => $student->nisn,
                    '[[ALAMAT_SISWA]]' => $student->alamat ?? '-',
                    '[[KELAS]]' => $class->nama_kelas,
                    '[[SEMESTER]]' => $semester, // Or Active Period Name
                    '[[TAHUN_AJARAN]]' => $activeYear->nama_tahun,
                    '[[NAMA_SEKOLAH]]' => $school->nama_sekolah,
                    '[[KEPALA_SEKOLAH]]' => $school->kepala_sekolah ?? '......................',
                    '[[WALI_KELAS]]' => $class->wali_kelas->name ?? '......................',
                    '[[TANGGAL_RAPOR]]' => $tempatRapor . ', ' . $tglRapor,
                    '[[TABEL_NILAI]]' => $htmlNilai,
                    '[[TABEL_EKSKUL]]' => $htmlEkskul,
                    '[[TABEL_PRESTASI]]' => $htmlPrestasi,
                    '[[TABEL_KEPRIBADIAN]]' => $htmlKepribadian,
                    '[[TABEL_KETIDAKHADIRAN]]' => $htmlAbsensi,
                    '[[CATATAN_WALI]]' => $note,
                    '[[STATUS_KENAIKAN]]' => $decisionText,
                    
                    // Stats
                    '[[JUMLAH_NILAI]]' => $myStats['total'],
                    '[[RATA_RATA]]' => $myStats['average'],
                    '[[PERINGKAT]]' => $myStats['rank'],
                    '[[TOTAL_SISWA]]' => $myStats['count'],
                 ];

                 // 2b. HANDLE LOOPS Custom (Prioritas sebelum replace biasa)
                 // LOOP NILAI
                 if (preg_match_all('/\[\[LOOP_NILAI_START\]\](.*?)\[\[LOOP_NILAI_END\]\]/s', $template->content, $matches)) {
                    foreach ($matches[1] as $index => $templateBlock) {
                        $compiledRows = '';
                        $no = 1;
                        
                        // Iterate Mapels (Same logic as manual table)
                        foreach ($mapelGroups as $cat => $group) {
                             // Optional: Add Category Header logic if needed? 
                             // For now plain list as requested normally.
                             foreach ($group as $mapelItem) {
                                 $nilaiObj = $cumulativeGrades[$mapelItem->id_mapel][$activePeriod->id] ?? null;
                                 $kkm = $kkmMapels[$mapelItem->id_mapel] ?? $globalKkm;
                                 
                                 // Format Values
                                 $valNilai = $nilaiObj ? $nilaiObj->nilai_akhir : '-';
                                 $valPredikat = $nilaiObj ? $nilaiObj->predikat : '-';

                                 $row = $templateBlock;
                                 $row = str_replace('[[NO]]', $no++, $row);
                                 $row = str_replace('[[MAPEL]]', $mapelItem->mapel->nama_mapel, $row);
                                 $row = str_replace('[[KKM]]', $kkm, $row);
                                 $row = str_replace('[[NILAI]]', $valNilai, $row);
                                 $row = str_replace('[[PREDIKAT]]', $valPredikat, $row);
                                 
                                 $compiledRows .= $row;
                             }
                        }
                        // Update content (using temporary var to avoid overwriting original template object in memory if cached)
                        $content = str_replace($matches[0][$index], $compiledRows, $template->content);
                    }
                 } else {
                     $content = $template->content;
                 }
                 
                 // 3. Replace Standard Variables
                 foreach ($vars as $key => $val) {
                     $content = str_replace($key, $val, $content);
                 }

                 return view('reports.custom_print', [
                    'content' => $content,
                    'margins' => $template->margins ?? ['top'=>10,'right'=>10,'bottom'=>10,'left'=>10],
                    'orientation' => $template->orientation,
                    'title' => 'Rapor - ' . $student->nama_lengkap
                 ]);
            }
        }
        // 13. Fetch Predicate Rules (Fix for Hardcoded Logic)
        $predicateRules = DB::table('predikat_nilai')
            ->where('jenjang', $class->jenjang->kode)
            ->orderBy('min_score', 'desc')
            ->get();

        // 14. Fetch Titimangsa (Report Date & Place)
        $jenjangKey = strtolower($class->jenjang->kode); // mi or mts
        $titimangsa = \App\Models\GlobalSetting::val('titimangsa_' . $jenjangKey);
        $titimangsaTempat = \App\Models\GlobalSetting::val('titimangsa_tempat_' . $jenjangKey);

        return view('reports.rapor_print', compact(
            'student', 'class', 'activeYear', 'allPeriods', 'activePeriod', 
            'mapelGroups', 'cumulativeGrades', 'attendance', 'remarks', 'ekskuls', 'school',
            'stats', 'annualStats', 'totalStudents', 'kkmMapels', 'globalKkm', 'statusNaik', 'decisionText', 'periodSlots',
            'predicateRules', 'titimangsa', 'titimangsaTempat'
        ));
    }
    public function printCover($studentId)
    {
        $data = $this->getStudentReportData($studentId);
        
        // Custom Cover Template
        $template = ReportTemplate::getActive('cover');
        if($template) {
             $student = $data['student'];
             $class = $data['class'];
             $school = $data['school'];
             $year = $data['activeYear'];

             $vars = [
                '[[NAMA_SISWA]]' => $student->nama_lengkap,
                '[[NIS]]' => $student->nis,
                '[[NISN]]' => $student->nisn,
                '[[KELAS]]' => $class->nama_kelas,
                '[[TAHUN_AJARAN]]' => $year->nama_tahun,
                '[[NAMA_SEKOLAH]]' => $school->nama_sekolah,
                '[[ALAMAT_SEKOLAH]]' => $school->alamat,
                '[[NSM]]' => $school->nsm,
                '[[NPSN]]' => $school->mpsn ?? $school->npsn,
             ];

             $content = $template->content;
             foreach ($vars as $key => $val) {
                 $content = str_replace($key, $val, $content);
             }

             return view('reports.custom_print', [
                'content' => $content,
                'margins' => $template->margins ?? ['top'=>10,'right'=>10,'bottom'=>10,'left'=>10],
                'orientation' => $template->orientation,
                'title' => 'Cover - ' . $student->nama_lengkap
             ]);
        }

        return view('reports.cover_print', $data);
    }

    public function printBiodata($studentId)
    {
        $data = $this->getStudentReportData($studentId);
        return view('reports.biodata_print', $data);
    }

    public function printClass($classId)
    {
        $class = Kelas::with(['wali_kelas', 'jenjang', 'tahun_ajaran'])->findOrFail($classId);
        $activeYear = $class->tahun_ajaran; // Or centralized active year? usually class belongs to year.
        
        // 1. Get All Students
        $students = $class->anggota_kelas()->with('siswa')->get()->sortBy('siswa.nama_lengkap');
        
        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa di kelas ini.');
        }

        $reports = [];
        
        // 2. Loop and Gather Data
        foreach ($students as $ak) {
            $reports[] = $this->gatherReportData($ak->siswa, $class, $activeYear);
        }

        // Fetch Titimangsa
        $jenjangKey = strtolower($class->jenjang->kode);
        $titimangsa = \App\Models\GlobalSetting::val('titimangsa_' . $jenjangKey);
        $titimangsaTempat = \App\Models\GlobalSetting::val('titimangsa_tempat_' . $jenjangKey);

        return view('reports.rapor_print_all', compact('class', 'activeYear', 'reports', 'titimangsa', 'titimangsaTempat'));
    }

    private function gatherReportData($student, $class, $activeYear)
    {
         // 3. Fetch All Periods for this Year (Sort by name or ID)
        $allPeriods = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('lingkup_jenjang', $class->jenjang->kode)
            ->orderBy('id', 'asc') 
            ->get();
            
        // 4. Fetch Grades for ALL Periods
        $rawGrades = NilaiSiswa::where('id_siswa', $student->id)
            ->where('id_kelas', $class->id)
            ->whereIn('id_periode', $allPeriods->pluck('id'))
            ->get();

    // [MOD] Apply Custom Formula (If Active)
    $jenjang = strtolower($class->jenjang->kode ?? 'mi');
    $context = ($jenjang === 'mts') ? 'rapor_mts' : 'rapor_mi';
    $activeFormula = GradingFormula::where('context', $context)->where('is_active', true)->first();

    if ($activeFormula) {
        foreach ($rawGrades as $grade) {
            // Prepare vars (Mapping from DB columns to Formula Variables)
            $vars = [
                '[Rata_PH]' => $grade->rata_ph ?? 0,
                '[Nilai_PTS]' => $grade->nilai_pts ?? 0,
                '[Nilai_PAS]' => $grade->nilai_pas ?? 0,
                '[Nilai_Sem_1]' => 0, // Not available in single period context usually, or fetch if needed
                '[Nilai_Sem_2]' => 0,
                // Add more if needed. For now, standard Rapor vars.
            ];
            
            // Recalculate
            $newScore = FormulaEngine::calculate($activeFormula->formula, $vars);
            
            // Override object property (InMemory)
            if ($newScore > 0) {
                // Round to 0 decimal for Rapor usually? Or 2? 
                // Let's keep existing precision or clean integer if user enforced.
                // Assuming Rapor uses integer usually.
                $grade->nilai_akhir = round($newScore);
            }
        }
    }
            
        $cumulativeGrades = [];
        foreach ($rawGrades as $g) {
            $cumulativeGrades[$g->id_mapel][$g->id_periode] = $g;
        }

        // 5. Fetch Mapels (Grouped & Sorted)
        $mapelGroups = PengajarMapel::with('mapel')
            ->where('id_kelas', $class->id)
            ->get()
            ->sortBy(function($item) {
                return ($item->mapel->kategori ?? 'Z') . '#' . $item->mapel->nama_mapel;
            })
            ->groupBy('mapel.kategori')
            ->sortKeys(); 

        // 6. Fetch Attendance & Remarks
        $attendance = DB::table('catatan_kehadiran')
            ->where('id_siswa', $student->id)
            ->where('id_kelas', $class->id)
            ->whereIn('id_periode', $allPeriods->pluck('id'))
            ->get()
            ->keyBy('id_periode');

        $remarks = DB::table('catatan_wali_kelas')
            ->where('id_siswa', $student->id)
            ->where('id_kelas', $class->id)
            ->whereIn('id_periode', $allPeriods->pluck('id'))
            ->orderBy('id_periode', 'asc')
            ->get()
            ->keyBy('id_periode');

        // 7. Ekskuls
        $ekskuls = DB::table('nilai_ekskul')
            ->join('ekstrakurikuler', 'nilai_ekskul.id_ekskul', '=', 'ekstrakurikuler.id')
            ->where('nilai_ekskul.id_siswa', $student->id)
            ->where('nilai_ekskul.id_kelas', $class->id)
            ->whereIn('nilai_ekskul.id_periode', $allPeriods->pluck('id'))
            ->select('ekstrakurikuler.nama_ekskul', 'nilai_ekskul.predikat as nilai', 'nilai_ekskul.keterangan', 'nilai_ekskul.id_periode')
            ->get()
            ->groupBy('id_periode');

        // 8. School Identity (Fetch by Jenjang)
    $targetJenjang = $class->jenjang->kode ?? 'MI';
    $school = \App\Models\SchoolIdentity::where('jenjang', $targetJenjang)->first();
    
    // Fallback to default (MI) if specific jenjang config not found
    if (!$school) {
        $school = \App\Models\SchoolIdentity::first();
    }

    if (!$school) {
         $school = new \App\Models\SchoolIdentity([
            'nama_sekolah' => '[DATA SEKOLAH BELUM DIISI]',
            'alamat' => '-',
         ]);
    }

        // 9. Calculate Statistics
        $stats = [];
        $totalStudents = $class->anggota_kelas()->count();

        foreach ($allPeriods as $p) {
             // Get all students' totals for this period/class to determine rank
            $classGrades = NilaiSiswa::select('id_siswa', DB::raw('SUM(nilai_akhir) as total_score'))
                ->where('id_kelas', $class->id)
                ->where('id_periode', $p->id)
                ->groupBy('id_siswa')
                ->orderBy('total_score', 'desc')
                ->get();
            
            $myStats = $classGrades->where('id_siswa', $student->id)->first();
            $myTotal = $myStats ? $myStats->total_score : 0;
            
            $rank = '-';
            if ($myStats) {
                 $rank = $classGrades->search(function($item) use ($student) {
                     return $item->id_siswa == $student->id;
                 }) + 1;
            }

            $mapelCount = NilaiSiswa::where('id_siswa', $student->id)
                ->where('id_periode', $p->id)
                ->count();
            
            $myAverage = $mapelCount > 0 ? $myTotal / $mapelCount : 0;

            $stats[$p->id] = [
                'total' => $myTotal,
                'average' => round($myAverage, 2),
                'rank' => $rank,
                'count' => $totalStudents
            ];
        }
        
        // 10. Fetch KKM
        $kkmMapels = DB::table('kkm_mapel')
            ->where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang_target', $class->jenjang->kode)
            ->pluck('nilai_kkm', 'id_mapel');
            
        $globalKkm = DB::table('grading_settings')->where('jenjang', $class->jenjang->kode)->value('kkm_default') ?? 70;

        // 11. Fetch Promotion Decision (Auto-Healing)
        $promotion = DB::table('promotion_decisions')
            ->where('id_siswa', $student->id)
            ->where('id_kelas', $class->id)
            ->where('id_tahun_ajaran', $activeYear->id)
            ->first();

        // If Missing, Trigger Auto-Calculation for this Class
        if (!$promotion) {
             // Use app() to instantiate controller method safely
             try {
                app(\App\Http\Controllers\PromotionController::class)->calculate($class->id);
                // Refetch
                $promotion = DB::table('promotion_decisions')
                    ->where('id_siswa', $student->id)
                    ->where('id_kelas', $class->id)
                    ->where('id_tahun_ajaran', $activeYear->id)
                    ->first();
             } catch (\Exception $e) {
                 // Log error or ignore, fallback to dots
             }
        }
        
        $decisionText = '......................';
        $statusNaik = null; 
        
        if ($promotion) {
            if ($promotion->final_decision === 'promoted' || $promotion->final_decision === 'graduated') {
                $statusNaik = true;
                $currentLevel = $class->tingkat_kelas ?? (int) filter_var($class->nama_kelas, FILTER_SANITIZE_NUMBER_INT);
                
                // Final Year Logic Check
                $isFinal = in_array($currentLevel, [6, 9, 12]);
                
                if ($isFinal || $promotion->final_decision === 'graduated') {
                     $decisionText = "LULUS";
                } else {
                     $nextLevel = $currentLevel + 1;
                     $decisionText = "Naik ke Kelas " . $nextLevel; 
                }

                // Check probation note
                $activePeriod = $allPeriods->firstWhere('status', 'aktif') ?? $allPeriods->last();
                $pId = $activePeriod->id ?? 0;
                $note = $remarks[$pId] ?? null;
                if ($note && $note->status_kenaikan === 'naik_percobaan') {
                    $decisionText .= " (Percobaan)";
                }

            } elseif ($promotion->final_decision === 'retained' || $promotion->final_decision === 'not_graduated') {
                $statusNaik = false;
                $currentLevel = $class->tingkat_kelas ?? (int) filter_var($class->nama_kelas, FILTER_SANITIZE_NUMBER_INT);
                $isFinal = in_array($currentLevel, [6, 9, 12]);
                
                if ($isFinal || $promotion->final_decision === 'not_graduated') {
                    $decisionText = "TIDAK LULUS";
                } else {
                    $decisionText = "Tinggal di Kelas " . $class->nama_kelas;
                }
            }
        } else {
            // FALLBACK: Use Historical AnggotaKelas Status
            // Find enrollment for this specific class
            $enrollment = \App\Models\AnggotaKelas::where('id_siswa', $student->id)
                ->where('id_kelas', $class->id)
                ->first();
            
            if ($enrollment) {
                if ($enrollment->status === 'naik_kelas') {
                    $statusNaik = true;
                    $currentLevel = (int) filter_var($class->nama_kelas, FILTER_SANITIZE_NUMBER_INT);
                    $nextLevel = $currentLevel + 1;
                    $decisionText = "Naik ke Kelas " . $nextLevel;
                } elseif ($enrollment->status === 'tinggal_kelas') {
                     $statusNaik = false;
                     $decisionText = "Tinggal di Kelas " . $class->nama_kelas;
                } elseif ($enrollment->status === 'lulus') {
                     $statusNaik = true;
                     $decisionText = "LULUS";
                } elseif ($enrollment->status === 'keluar' || $enrollment->status === 'mutasi') {
                     $statusNaik = false;
                     $decisionText = "MUTASI / KELUAR";
                }
            }
        }
        
        $activePeriod = $allPeriods->firstWhere('status', 'aktif') ?? $allPeriods->last();
        $periodSlots = $allPeriods->map(function($p) {
             return is_numeric($p->nama_periode) 
                ? $p->nama_periode 
                : preg_replace('/[^0-9]/', '', $p->nama_periode);
        });

        // 12. Calculate Annual Ranking (Average of Annual Mapel Averages)
        $annualStats = [
            'total' => 0,
            'average' => 0,
            'rank' => '-'
        ];

        // We need to calculate this for ALL students in the class to get the rank
        // To avoid N+1 performance hit on Bulk Print, we can check if it's already passed or handle it efficiently.
        // For now, let's implement a per-student calculation which is safer but slower, OR a class-wide one if optimizing.
        // Given the requirement, let's do a Class-Wide fetch ONCE if not provided.
        // But since this function is 'gatherReportData', let's stick to calculating for this student properly relative to others.
        
        // Fetch ALL grades for this class for the WHOLE year
        $allStudentGrades = NilaiSiswa::where('id_kelas', $class->id)
            ->whereIn('id_periode', $allPeriods->pluck('id'))
            ->get()
            ->groupBy('id_siswa');
            
        $classAverages = []; // student_id => average
        
        foreach ($allStudentGrades as $sId => $grades) {
             // Group by Mapel
             $mapelGrades = $grades->groupBy('id_mapel');
             $mapelAvgs = [];

             foreach ($mapelGrades as $mId => $mGrades) {
                 if ($class->jenjang->kode == 'MI') {
                     // MI Logic: (Cawu 1 + Cawu 2 + Cawu 3) / 3
                     // Need to ensure we sum properly.
                     // Method A: Average of Grades. If a student has grade in C1, C2, C3, avg is (C1+C2+C3)/3.
                     // If student is missing C3, avg is (C1+C2)/2? Or (C1+C2)/3?
                     // Usually for "Kenaikan", if missing, it might be 0.
                     // But strict average implies dividing by COUNT of present periods.
                     // The user said: "Rata-rata dari 3 Cawu".
                     // Default Laravel `avg()` does exactly that (Sum / Count).
                     $mapelAvgs[] = $mGrades->avg('nilai_akhir');
                 } else {
                     // MTS Logic (Semester): Average of S1 + S2
                     $mapelAvgs[] = $mGrades->avg('nilai_akhir');
                 }
             }
             
             // Annual Average for Student = Average of Mapel Averages
             // This matches "Rata-rata Umum".
             $studentAnnualAvg = count($mapelAvgs) > 0 ? array_sum($mapelAvgs) / count($mapelAvgs) : 0;
             
             // Total Score (Jumlah Nilai Rata-Rata Mapel)
             $studentAnnualTotal = array_sum($mapelAvgs);
             
             $classAverages[$sId] = $studentAnnualAvg;
             $classTotals[$sId] = $studentAnnualTotal;
        }
        
        // Sort to find Rank
        arsort($classAverages);
        
        $myAnnualAvg = $classAverages[$student->id] ?? 0;
        $myAnnualTotal = $classTotals[$student->id] ?? 0;
        $myRank = array_search($student->id, array_keys($classAverages)) + 1;
        
        $annualStats = [
            'total' => number_format($myAnnualTotal, 2), // Sum of averages might have decimals
            'average' => number_format($myAnnualAvg, 2),
            'rank' => $myRank,
            'count' => count($classAverages)
        ];

        // 12. Determine Period Slots & Label
        $jenjangKode = strtoupper($class->jenjang->kode ?? 'MI');
        if ($jenjangKode === 'MTS') {
            $periodSlots = [1, 2];
            $periodLabel = 'Semester';
        } else {
             $periodSlots = [1, 2, 3];
             $periodLabel = 'Catur Wulan';
        }

        return compact(
            'student', 'class', 'activeYear', 'allPeriods', 'activePeriod', 
            'mapelGroups', 'cumulativeGrades', 'attendance', 'remarks', 'ekskuls', 'school', 'periodLabel',
            'stats', 'annualStats', 'totalStudents', 'kkmMapels', 'globalKkm', 'statusNaik', 'decisionText', 
            'periodSlots', 'periodLabel'
        );
    }
    
    private function getStudentReportData($studentId) 
    {
         // Legacy minimal data fetcher, kept if needed by other methods
         // ... implementation as before ...
         // But actually I can just leave it if I don't touch existing methods using it.
         // Wait, the existing file HAS getStudentReportData at the bottom.
         // I am REPLACING the bottom of the file. I should include it or not overwrite it erroneously.
         
         // 1. Get Student
        $student = Siswa::with(['anggota_kelas.kelas.wali_kelas', 'anggota_kelas.kelas.tahun_ajaran', 'anggota_kelas.kelas.jenjang'])
            ->findOrFail($studentId);

        // 2. Active Year
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        $activeClassMember = $student->anggota_kelas->filter(function($ak) use ($activeYear) {
            return $ak->kelas->id_tahun_ajaran == $activeYear->id;
        })->first();

        if (!$activeClassMember) {
            $activeClassMember = $student->anggota_kelas->first();
        }

        if (!$activeClassMember) {
            abort(404, 'Siswa belum masuk kelas manapun.');
        }

        $class = $activeClassMember->kelas;
        
        // 3. School Identity
        $school = \App\Models\SchoolIdentity::first();
        if (!$school) {
             $school = new \App\Models\SchoolIdentity([
                'nama_sekolah' => 'MADRASAH CONTOH',
                'alamat' => 'Alamat Belum Diisi',
            ]);
        }

        return compact('student', 'class', 'activeYear', 'school');
    }
    public function classAnalytics(Request $request, $classId)
    {
        $class = Kelas::with(['wali_kelas', 'jenjang', 'tahun_ajaran'])->findOrFail($classId);
        $activeYear = $class->tahun_ajaran;
        
        $periodes = Periode::where('id_tahun_ajaran', $activeYear->id)
             ->where('lingkup_jenjang', $class->jenjang->kode)
             ->get();

        $isAnnual = $request->period_id === 'annual';
        $periode = null;

        if ($isAnnual) {
            // Annual Mode: Use ALL periods
            $targetPeriodIds = $periodes->pluck('id');
        } else {
            // Single Period Mode
            if ($request->period_id) {
                $periode = $periodes->firstWhere('id', $request->period_id);
            } else {
                $periode = $periodes->firstWhere('status', 'aktif');
                if (!$periode) $periode = $periodes->last();
            }
            if (!$periode && !$isAnnual) return back()->with('error', 'Periode tidak ditemukan.');
            
            $targetPeriodIds = [$periode->id]; // Single ID array
        }

        // 2. Fetch Data (Filtered by Target Periods)
        $students = $class->anggota_kelas()->with('siswa')->get();
        
        // Grades
        $grades = NilaiSiswa::with('mapel')
            ->where('id_kelas', $class->id)
            ->whereIn('id_periode', $targetPeriodIds)
            ->get()
            ->groupBy('id_siswa');
            
        // Attendance
        $attendance = DB::table('catatan_kehadiran')
            ->where('id_kelas', $class->id)
            ->whereIn('id_periode', $targetPeriodIds)
            ->get()
            ->groupBy('id_siswa'); 

        // --- TREND ANALYSIS (PREPARATION) ---
        $previousRanks = [];
        $rankJourney = []; // For Annual View: ['Sem 1' => 10, 'Sem 2' => 1]
        
        if ($isAnnual) {
            // Annual Mode: Calculate Rank for EACH Period to show "Journey"
            foreach($periodes as $p) {
                 // Calculate Rank snapshot for this period
                 $pGrades = DB::table('nilai_siswa')
                    ->select('id_siswa', DB::raw('SUM(nilai_akhir) as total_score'))
                    ->where('id_kelas', $class->id)
                    ->where('id_periode', $p->id)
                    ->groupBy('id_siswa')
                    ->orderByDesc('total_score')
                    ->get();
                
                $pr = 1;
                foreach($pGrades as $pg) {
                    $rankJourney[$pg->id_siswa][] = [
                        'period' => $p->nama_periode,
                        'rank' => $pr++
                    ];
                }
            }
        
        } elseif ($periode) {
            // Single Period Mode: Find Previous Period in the same Academic Year
            $prevPeriode = $periodes->where('id', '<', $periode->id)->sortByDesc('id')->first();
            
            if ($prevPeriode) {
                // Fetch basic grades for Previous Period to Calculate Rank Snapshot
                $prevGrades = DB::table('nilai_siswa')
                    ->select('id_siswa', DB::raw('SUM(nilai_akhir) as total_score'))
                    ->where('id_kelas', $class->id)
                    ->where('id_periode', $prevPeriode->id)
                    ->groupBy('id_siswa')
                    ->orderByDesc('total_score')
                    ->get();
                
                // Assign Ranks
                $pRank = 1;
                foreach($prevGrades as $pg) {
                    $previousRanks[$pg->id_siswa] = $pRank++;
                }
            }
        }

        // 3. Calculate Stats & Rank
        $rankingData = [];
        
        foreach($students as $ak) {
            $sGrades = $grades[$ak->siswa->id] ?? collect([]);
            $sAttRecords = $attendance[$ak->siswa->id] ?? collect([]);
            
            // Calculate Scores
            if ($isAnnual) {
                // Annual Logic: Average of Mapel Averages (Same as Leger Rekap)
                $mapelGrades = $sGrades->groupBy('id_mapel');
                $mapelAvgs = [];
                foreach ($mapelGrades as $mId => $mGrades) {
                     $mapelAvgs[] = $mGrades->avg('nilai_akhir');
                }
                
                $totalScore = round(array_sum($mapelAvgs), 2); // Round to 2 decimals to allow Ties
                $count = count($mapelAvgs);
                $avgScore = $count > 0 ? $totalScore / $count : 0;
                $gradeCount = $count; // Count of Mapels
            } else {
                // Single Period Logic
                $totalScore = $sGrades->sum('nilai_akhir');
                $count = $sGrades->count();
                $avgScore = $count > 0 ? $totalScore / $count : 0;
                $gradeCount = $count;
            }
            
            // Calculate Absence & Personality (Latest Period)
            $absenceCount = 0;
            $personality = '-';
            
            // Get last attendance record for personality (most recent)
            $lastAtt = $sAttRecords->last();
            if ($lastAtt) {
                 // Instruction: Only take 'Kelakuan'
                 if (!empty($lastAtt->kelakuan) && $lastAtt->kelakuan != '-') {
                     $personality = $lastAtt->kelakuan;
                 }
            }

            foreach ($sAttRecords as $att) {
                $absenceCount += ($att->sakit ?? 0) + ($att->izin ?? 0) + ($att->tanpa_keterangan ?? 0);
            }
            
            $rankingData[] = [
                'student' => $ak->siswa,
                'total' => $totalScore,
                'avg' => $avgScore,
                'absence' => $absenceCount,
                'personality' => $personality,
                'grades_count' => $gradeCount,
                'tie_reason' => null,
                'prev_rank' => $previousRanks[$ak->siswa->id] ?? null,
                'rank_journey' => $rankJourney[$ak->siswa->id] ?? []
            ];
        }

        // 3. Fetch Promotion Decisions (Bulk)
        $promoDecisions = \Illuminate\Support\Facades\DB::table('promotion_decisions')
            ->where('id_kelas', $classId)
            ->where('id_tahun_ajaran', $activeYear->id)
            ->get()
            ->keyBy('id_siswa');
            
        // 4. Sort with Tie-Breaker Logic (Same for both)
        usort($rankingData, function($a, $b) {
            // 1. Total Score (Desc)
            if (abs($a['total'] - $b['total']) > 0.01) {
                return $b['total'] <=> $a['total'];
            }
            // 2. Absence Count (Asc) - LEAST ABSENCE WIN
            if ($a['absence'] !== $b['absence']) {
                return $a['absence'] <=> $b['absence'];
            }
            // 3. Name (Asc)
            return strcasecmp($a['student']->nama_lengkap, $b['student']->nama_lengkap);
        });

        // 5. Assign Ranks & Identify Ties
        $rank = 1;
        $prevData = null;
        
        foreach ($rankingData as &$data) {
            $data['rank'] = $rank++;
            $insight = [];
            $sid = $data['student']->id;

            // 1. Tie Breaker Check
            if ($prevData) {
                $scoreTie = abs($data['total'] - $prevData['total']) < 0.01;
                
                if ($scoreTie) {
                    if ($prevData['absence'] < $data['absence']) {
                        $prevData['tie_reason'] = "Menang di Kehadiran";
                        $data['tie_reason'] = "Kalah di Kehadiran";
                    } elseif ($prevData['absence'] == $data['absence']) {
                         $data['tie_reason'] = "Seri Mutlak (Abjad)";
                    }
                }
            }
            
            // 2. Behavioral Insights (The "Details" User wants)
            $isSmart = $data['avg'] >= 85; 
            $isDiligent = $data['absence'] <= 3;
            $isLazy = $data['absence'] >= 10;
            $isLowScore = $data['avg'] < 75;
            $isTopRank = $data['rank'] <= 3;
            $isLowRank = $data['rank'] > (count($rankingData) * 0.7); // Bottom 30%

            if ($data['rank'] == 1) {
                 $insight[] = "🏆 Juara Umum";
            } else {
                if ($isTopRank && $isDiligent) {
                    $insight[] = "🌟 Siswa Teladan (Pintar & Rajin)";
                } elseif ($isTopRank && $isLazy) {
                    $insight[] = "💡 Cerdas tapi Sering Absen";
                } elseif ($isLowRank && $isDiligent) {
                    $insight[] = "💪 Rajin tapi Nilai Kalah Bersaing";
                } elseif ($isLowRank && $isLazy) {
                    $insight[] = "⚠️ Perlu Perhatian Khusus";
                } elseif ($isLowScore) {
                    $insight[] = "Perlu Remedial";
                } elseif ($isDiligent) {
                    $insight[] = "Kehadiran Sangat Baik";
                }
            }

            // High Absence Warning
            if ($data['absence'] >= 10 && !$isLazy) { 
                 $insight[] = "⚠️ Awas: Absen Tinggi (" . $data['absence'] . ")";
            }
            
            // 3. Trend Analysis (Rising Star & Annual Journey)
            if ($isAnnual) {
                $journey = $data['rank_journey'];
                if (count($journey) >= 2) {
                    $firstRank = $journey[0]['rank'];
                    $lastRank = end($journey)['rank'];
                    $diff = $firstRank - $lastRank; 
                    
                    $data['trend_diff'] = $diff;
                    $data['start_rank'] = $firstRank;
                    $data['end_rank'] = $lastRank;
                    
                    // Detailed Trend Status for Annual
                    if ($diff >= 5) {
                         $insight[] = "👑 Raja Comeback (Naik Drastis)";
                         $data['trend_status'] = 'comeback';
                    } elseif ($diff >= 1) {
                         $data['trend_status'] = 'improved';
                    } elseif ($diff <= -5) {
                         $insight[] = "📉 Mengalami Penurunan Signifikan";
                         $data['trend_status'] = 'dropped';
                    } elseif (abs($diff) <= 1 && $data['rank'] <= 3) {
                         $insight[] = "🛡️ Dewa Stabil (Konsisten Top)";
                         $data['trend_status'] = 'stable_high';
                    } elseif (abs($diff) <= 1) {
                         $insight[] = "⚓ Performa Stabil";
                         $data['trend_status'] = 'stable';
                    } else {
                         // Default fallbacks
                         $data['trend_status'] = ($diff > 0) ? 'up' : 'down';
                    }
                }
            } elseif (isset($data['prev_rank'])) {
                // Standard Period Logic (Existing)
                $diff = $data['prev_rank'] - $data['rank'];
                if ($diff >= 3) {
                    $insight[] = "🚀 Rising Star (Naik $diff Peringkat)";
                    $data['trend_status'] = 'rising';
                } elseif ($diff <= -3) {
                    $insight[] = "📉 Perlu Evaluasi (Turun " . abs($diff) . " Peringkat)";
                    $data['trend_status'] = 'falling';
                } elseif ($diff > 0) {
                     $data['trend_status'] = 'up'; // Small improvement
                } elseif ($diff < 0) {
                     $data['trend_status'] = 'down'; // Small drop
                } else {
                     $data['trend_status'] = 'flat';
                }
                $data['trend_diff'] = $diff;
            } else {
                $data['trend_status'] = null;
            }

            // 4. SYNC WITH PROMOTION STATUS (USER REQUEST)
            // Priority Override: If Retained/Failed, this is the most important status.
            if (isset($promoDecisions[$sid])) {
                $promo = $promoDecisions[$sid];
                // 'promoted', 'retained', 'conditional', 'graduated', 'not_graduated'
                
                if ($promo->final_decision == 'retained') {
                    // Force Wipe other positive insights if retained
                    $insight = ["⛔ Tinggal Kelas"]; 
                } elseif ($promo->final_decision == 'not_graduated') {
                    $insight = ["⛔ Tidak Lulus"];
                } elseif ($promo->final_decision == 'conditional') {
                    // Prepend Warning
                    array_unshift($insight, "⚠️ Naik Bersyarat");
                }
            }
            
            // Merge Logic: Tie Reason is ALWAYS the Prefix if exists
            if ($data['tie_reason']) {
                $data['insight'] = "⚖️ " . $data['tie_reason'];
            } else {
                $data['insight'] = implode(" • ", $insight);
            }

            $prevData = &$data; 
        }
        unset($data);

        // --- ADVANCED ANALYTICS START ---
        
        // 1. Mapel Difficulty Analysis ("Mapel Neraka" vs "Surga")
        // Aggregate all grades in this selection
        $allMapelGrades = collect();
        if ($isAnnual) {
             // Re-fetch all grades flat for mapel analysis? Or reuse $grades structure?
             // $grades is grouped by id_siswa. Need to pivot to Group By ID Mapel.
             foreach($grades as $sGrades) {
                 foreach($sGrades as $grade) {
                     $allMapelGrades->push($grade);
                 }
             }
        } else {
            // Flatten the nested collection from groupBy('id_siswa')
             $allMapelGrades = $grades->flatten();
        }

        $mapelStats = $allMapelGrades->groupBy('id_mapel')->map(function ($row) {
            return [
                'avg' => $row->avg('nilai_akhir'),
                'count' => $row->count(),
                'mapel' => $row->first()->mapel ?? null // Eager loading might be needed on initial query
            ];
        })->sortBy('avg'); // Low to High

        $mapelAnalysis = [
            'hardest' => $mapelStats->first(), // Lowest Avg
            'easiest' => $mapelStats->last(),  // Highest Avg
            'all' => $mapelStats
        ];
        
        // 2. Anomaly Detection (The "Syainur" Paradox)
        // High Rank (Top 5) BUT High Absence (>= 10)
        $anomalies = collect($rankingData)->filter(function($student) {
            return $student['rank'] <= 5 && $student['absence'] >= 10;
        });

        // 6. Podium (Top 3)
        $podium = array_slice($rankingData, 0, 3);

        return view('reports.class_analytics', compact(
            'class', 'periode', 'periodes', 'rankingData', 'podium', 'isAnnual', 
            'mapelAnalysis', 'anomalies'
        ));
    }
}
