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
        // 1. Initial Checks
        if (!$this->checkActiveYear()) {
             return redirect()->back()->with('error', '⚠️ AKSES DITOLAK: Periode terkunci atau bukan tahun aktif.');
        }

        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // 2. Class Selection
        $userId = Auth::id();
        $user = Auth::user();
        
        // Admin Access
        if ($user->role === 'admin' || $userId == 1 || $user->isStaffTu()) {
            $allClasses = Kelas::where('id_tahun_ajaran', $activeYear->id)->orderBy('nama_kelas')->get();
        } else {
            // Wali Kelas Access
            $allClasses = Kelas::where('id_tahun_ajaran', $activeYear->id)
                ->where('id_wali', $userId)
                ->orderBy('nama_kelas')
                ->get();
        }

        $selectedClass = $allClasses->first();
        if ($request->has('class_id')) {
            $selectedClass = $allClasses->where('id', $request->class_id)->first() ?? $selectedClass;
        }

        $students = collect([]);
        $metrics = ['total' => 0, 'promoted' => 0, 'retained' => 0];
        $isLocked = false;
        $isFinalYear = false;
        $pageContext = [
            'type' => 'promotion',
            'title' => 'Kenaikan Kelas',
            'success_label' => 'Naik Kelas',
            'fail_label' => 'Tinggal Kelas',
            'success_badge' => 'Naik Kelas',
            'fail_badge' => 'Tinggal Kelas'
        ];

        // 3. Logic Engine
        if ($selectedClass) {
            // Check Lock (Manual Override Logic)
            $isLocked = DB::table('promotion_decisions')
                ->where('id_kelas', $selectedClass->id)
                ->where('id_tahun_ajaran', $activeYear->id)
                ->whereNotNull('override_by')
                ->exists();

            // Run Calculation
            $this->calculate($selectedClass->id);

            // Fetch Results
            $students = DB::table('promotion_decisions')
                ->join('siswa', 'promotion_decisions.id_siswa', '=', 'siswa.id')
                ->where('promotion_decisions.id_kelas', $selectedClass->id)
                ->where('promotion_decisions.id_tahun_ajaran', $activeYear->id)
                ->select('siswa.nama_siswa', 'siswa.nis', 'promotion_decisions.*')
                ->orderBy('siswa.nama_siswa')
                ->get();

            $metrics['total'] = $students->count();
            $metrics['promoted'] = $students->whereIn('system_recommendation', ['promoted', 'graduated', 'conditional'])->count();
            $metrics['retained'] = $students->whereIn('system_recommendation', ['retained', 'not_graduated'])->count();


            // --- REVAMPED FINAL YEAR LOGIC WITH LOGGING ---
            $debugLog = [];
            
            // 1. Force Load Jenjang
            if (!$selectedClass->relationLoaded('jenjang')) {
                $selectedClass->load('jenjang');
            }
            
            $jenjangCode = optional($selectedClass->jenjang)->kode;
            $debugLog[] = "Raw Jenjang: " . ($jenjangCode ?? 'NULL');
            
            if (!$jenjangCode) {
                // Fallback: Guess from name
                if (stripos($selectedClass->nama_kelas, 'MTs') !== false) {
                    $jenjangCode = 'MTS';
                    $debugLog[] = "Fallback Jenjang: MTS (from name)";
                } elseif (stripos($selectedClass->nama_kelas, 'MI') !== false) {
                    $jenjangCode = 'MI';
                    $debugLog[] = "Fallback Jenjang: MI (from name)";
                } else {
                    $debugLog[] = "Fallback Jenjang: Failed";
                }
            }
            $jenjangCode = strtoupper($jenjangCode);
            $debugLog[] = "Normalized Jenjang: $jenjangCode";
            
            $grade = (int) filter_var($selectedClass->nama_kelas, FILTER_SANITIZE_NUMBER_INT);
            $debugLog[] = "Grade Level: $grade";
            
            $finalGradeMI = (int) \App\Models\GlobalSetting::val('final_grade_mi', 6);
            $finalGradeMTS = (int) \App\Models\GlobalSetting::val('final_grade_mts', 9);
            $debugLog[] = "Config MI: $finalGradeMI, MTS: $finalGradeMTS";

            // Strict Logic
            if ($jenjangCode === 'MI') {
                if ($grade == $finalGradeMI) {
                    $isFinalYear = true;
                    $debugLog[] = "DECISION: FINAL YEAR (MI Match)";
                } else {
                    $debugLog[] = "DECISION: NOT FINAL (MI mismatch $grade != $finalGradeMI)";
                }
            } elseif ($jenjangCode === 'MTS') {
                 if ($grade == $finalGradeMTS) {
                     $isFinalYear = true; // Config match
                     $debugLog[] = "DECISION: FINAL YEAR (MTS Config Match)";
                 } elseif ($finalGradeMTS == 9 && $grade == 3) {
                     $isFinalYear = true; // Legacy support (Class 3 MTs)
                     $debugLog[] = "DECISION: FINAL YEAR (MTS Legacy 3==9 Match)";
                 } else {
                     $debugLog[] = "DECISION: NOT FINAL (MTS mismatch Grade $grade != $finalGradeMTS)";
                 }
            } else {
                $debugLog[] = "DECISION: NOT FINAL (Unknown Jenjang '$jenjangCode')";
            }

            // --- FINAL PERIOD VALIDATION ---
            // Even if it's the final year grade, we must be in the final semester/cawu
            if ($isFinalYear) {
                 $activePeriodVal = \App\Models\Periode::where('status', 'aktif')->first();
                 if ($activePeriodVal) {
                     $pName = strtolower($activePeriodVal->nama_periode);
                     // If explicit Odd Semester -> Revoke Graduation Status
                     if (str_contains($pName, 'ganjil') || str_contains($pName, 'semester 1') || str_contains($pName, 'cawu 1') || str_contains($pName, 'cawu 2')) {
                         $isFinalYear = false;
                         $debugLog[] = "DECISION: FINAL YEAR REVOKED (Period '$pName' is not final)";
                     }
                 }
            }

            if ($isFinalYear) {
                $pageContext = [
                    'type' => 'graduation',
                    'title' => 'Kelulusan Akhir',
                    'success_label' => 'LULUS',
                    'fail_label' => 'TIDAK LULUS',
                    'success_badge' => 'LULUS',
                    'fail_badge' => 'TIDAK LULUS'
                ];
            }
        }

        // 4. Access Control (Period Check)
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)->get();
        $activePeriod = $periods->firstWhere('status', 'aktif');
        $warningMessage = null;

        if ($activePeriod) {
            $isLast = $periods->last() && $activePeriod->id === $periods->last()->id;
            if (!$isLast && !$user->role === 'admin') {
                return redirect()->route('dashboard')->with('error', 'Halaman ini hanya aktif di periode akhir.');
            }
            if (!$isLast) {
                 $warningMessage = "⚠️ PERINGATAN: Periode saat ini ({$activePeriod->nama_periode}) BUKAN periode akhir.";
            }
        }

        return view('promotion.index', compact(
            'allClasses', 
            'selectedClass', 
            'students', 
            'metrics', 
            'isLocked', 
            'warningMessage', 
            'isFinalYear',
            'pageContext',
            'debugLog'
        ));
    }

    // THE LOGIC ENGINE
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
        // 1. Grades (WITH MAPEL NAME)
        $allGrades = NilaiSiswa::with('mapel')
            ->whereIn('id_siswa', $studentIds)
            ->where('id_kelas', $kelasId)
            ->whereIn('id_periode', $periodIds)
            ->get()
            ->groupBy('id_siswa');

        // 2. KKM (Mapel specific)
        $allKkm = \App\Models\KkmMapel::where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang_target', $jenjang)
            ->get()
            ->keyBy('id_mapel'); 

        // 3. Attendance (Strict Class Filter)
        $allAttendance = DB::table('catatan_kehadiran')
            ->whereIn('id_siswa', $studentIds)
            ->where('id_kelas', $kelasId) // STRICT FILTER
            ->whereIn('id_periode', $periodIds)
            ->get()
            ->groupBy('id_siswa');

        // 4. Weights (Bobot) - Kept for reference though unused here
        $weights = \App\Models\BobotPenilaian::where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang', $jenjang)
            ->first();
        
        // 5. Ijazah Grades (For Final Year Logic - INFO ONLY)
        $allIjazah = DB::table('nilai_ijazah')
            ->whereIn('id_siswa', $studentIds)
            ->get()
            ->groupBy('id_siswa');
            
        $minLulusIjazah = (float) \App\Models\GlobalSetting::val('ijazah_min_lulus', 60);
        $totalDays = \App\Models\GlobalSetting::val('total_effective_days', 220); 
        if ($totalDays <= 0) $totalDays = 220;

        foreach ($students as $student) {
            $report = []; 
            $fail = false;
            $sid = $student->id_siswa;

            // Get Student Grades
            $studentGrades = $allGrades[$sid] ?? collect([]);
            $mapelGrades = $studentGrades->groupBy('id_mapel');
            
            $kkmFailures = 0;
            $totalScore = 0;
            $mapelCount = 0;
            $failedMapels = [];

            foreach ($mapelGrades as $mapelId => $vals) {
                // Determine Average
                $avgMapel = $vals->avg('nilai_akhir');
                
                $totalScore += $avgMapel;
                $mapelCount++;

                // Lookup KKM
                $kkmVal = isset($allKkm[$mapelId]) ? $allKkm[$mapelId]->nilai_kkm : ($settings->kkm_default ?? 70);

                if ($avgMapel < $kkmVal) {
                    $kkmFailures++;
                    // Get Mapel Name safely
                    $mapelName = optional($vals->first()->mapel)->nama_mapel ?? 'Mapel Unknown';
                    // Shorten name if too long? No, full detail is better for Sidang.
                    $failedMapels[] = "$mapelName (" . round($avgMapel) . ")";
                }
            }

            $finalAvg = $mapelCount > 0 ? round($totalScore / $mapelCount, 2) : 0;
            $isConditional = false;

            // LOGIC CHECK KKM
            if ($kkmFailures > $settings->promotion_max_kkm_failure) {
                $fail = true;
                $report[] = "Gagal KKM ($kkmFailures): " . implode(', ', $failedMapels);
            } elseif ($kkmFailures == $settings->promotion_max_kkm_failure) {
                $isConditional = true;
                $report[] = "Perhatian Batas KKM: " . implode(', ', $failedMapels);
            }

            // Attendance Logic
            $studentAttendance = $allAttendance[$sid] ?? collect([]);
            $totalSakit = $studentAttendance->sum('sakit');
            $totalIzin = $studentAttendance->sum('izin');
            $totalAlpa = $studentAttendance->sum('tanpa_keterangan');
            
            // Recalculate Total
            $attendance = round((($totalDays - $totalAlpa) / $totalDays) * 100);

            if ($attendance < $settings->promotion_min_attendance) {
                $fail = true;
                $report[] = "Kehadiran {$attendance}% (A: $totalAlpa, S: $totalSakit, I: $totalIzin)";
            } elseif ($attendance >= $settings->promotion_min_attendance && $attendance < ($settings->promotion_min_attendance + 5)) {
                $isConditional = true; // Still pass but warn
                $report[] = "Perhatian Absensi: A: $totalAlpa, S: $totalSakit, I: $totalIzin";
            }

            // Attitude Logic
            $attitude = 'B';
            $badAttitudes = [];
            foreach ($studentAttendance as $rec) {
                if ($rec->kelakuan === 'Kurang') $badAttitudes[] = 'Kelakuan';
                if ($rec->kerajinan === 'Kurang') $badAttitudes[] = 'Kerajinan';
                if ($rec->kebersihan === 'Kurang') $badAttitudes[] = 'Kebersihan';
            }
            if (!empty($badAttitudes)) $attitude = 'C';

            // Attitude Check
            $gradesOrder = ['A', 'B', 'C', 'D'];
            $minIdx = array_search($settings->promotion_min_attitude, $gradesOrder);
            $currIdx = array_search($attitude, $gradesOrder);
            
            if ($currIdx > $minIdx) {
                $fail = true;
                $report[] = "Sikap {$attitude} (Kurang: " . implode(', ', array_unique($badAttitudes)) . ")";
            }
 
            // DECISION
            if ($fail) {
                $recommendation = 'retained';
            } elseif ($isConditional) {
                $recommendation = 'conditional';
            } else {
                $recommendation = 'promoted';
            }
            
            // RELOAD Jenjang
            if (!$kelas->relationLoaded('jenjang')) $kelas->load('jenjang');
            $jenjangCode = strtoupper(trim(optional($kelas->jenjang)->kode));

            // Check Graduation (Is Final Year?)
            $gradeLevel = $this->parseGradeLevel($kelas);
            $finalGradeMI = (int) \App\Models\GlobalSetting::val('final_grade_mi', 6);
            $finalGradeMTS = (int) \App\Models\GlobalSetting::val('final_grade_mts', 9);
            $isMts = $jenjangCode == 'MTS' || stripos($kelas->nama_kelas, 'mts') !== false;
            $isMi = $jenjangCode == 'MI' || stripos($kelas->nama_kelas, 'mi') !== false || stripos($kelas->nama_kelas, 'ibtidaiyah') !== false;
            $isFinalYear = ($isMi && $gradeLevel == $finalGradeMI) || 
                           ($isMts && ($gradeLevel == $finalGradeMTS || ($finalGradeMTS == 9 && $gradeLevel == 3)));

            if ($isFinalYear) {
                if ($recommendation == 'promoted' || $recommendation == 'conditional') {
                    $recommendation = 'graduated';
                } elseif ($recommendation == 'retained') {
                    $recommendation = 'not_graduated';
                }

                // --- INFO: IJAZAH STATUS (conditionally show) ---
                $currPeriod = \App\Models\Periode::where('id', $activeYear->id)->where('status', 'aktif')->value('nama_periode'); 
                if (!$currPeriod) $currPeriod = \App\Models\Periode::where('status', 'aktif')->value('nama_periode');

                $isFinalPeriod = false;
                if ($currPeriod) {
                    $pName = strtolower($currPeriod);
                    if (str_contains($pName, 'genap') || str_contains($pName, 'semester 2') || str_contains($pName, 'cawu 3')) {
                        $isFinalPeriod = true;
                    }
                }

                if ($isFinalPeriod) {
                    $ijazahGrades = $allIjazah[$sid] ?? collect([]);
                    $ijazahSum = 0;
                    $ijazahCount = 0;
                    foreach ($ijazahGrades as $g) {
                        if ($g->nilai_ijazah > 0) {
                            $ijazahSum += $g->nilai_ijazah;
                            $ijazahCount++;
                        }
                    }
                    
                    if ($ijazahCount > 0) {
                        $ijazahAvg = $ijazahSum / $ijazahCount;
                        if ($ijazahAvg >= $minLulusIjazah) {
                            $report[] = "Info: Ijazah LULUS (Avg: " . round($ijazahAvg, 2) . ")";
                        } else {
                            $report[] = "PERHATIAN: Ijazah TIDAK LULUS (Avg: " . round($ijazahAvg, 2) . " < $minLulusIjazah)";
                        }
                    }
                }
            }
            
            $reason = count($report) > 0 ? implode(' | ', $report) : 'Memenuhi semua syarat.'; // Separator changed to pipe for clarity

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
            
            // Auto-Sync Override Logic
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

        // --- SYNC TO ANGGOTA KELAS (BUKU INDUK CONTROL) ---
        // Ensures 'Keterangan' in Student Profile is Automatic
        $decisions = DB::table('promotion_decisions')
            ->where('id_tahun_ajaran', $activeYear->id)
            ->get();

        $syncCount = 0;
        foreach ($decisions as $dec) {
            $status = 'aktif';
            $final = $dec->final_decision ?? $dec->system_recommendation; // Use system if final is null? No, prefer final.
            
            // Map Decision to AnggotaKelas Status
            if ($final == 'promoted' || $final == 'conditional') $status = 'naik_kelas';
            elseif ($final == 'retained') $status = 'tinggal_kelas';
            elseif ($final == 'graduated') $status = 'lulus';
            elseif ($final == 'not_graduated') $status = 'tinggal_kelas'; 
            
            // Update AnggotaKelas
            // We need to match precise class member record
            $updated = AnggotaKelas::where('id_siswa', $dec->id_siswa)
                ->where('id_kelas', $dec->id_kelas)
                ->update(['status' => $status]);
            
            if ($updated) $syncCount++;
        }

        return back()->with('success', "Status Kenaikan Kelas BERHASIL DIKUNCI PERMANEN. $affected Data Di-finalisasi. $syncCount Data Riwayat/Buku Induk diperbarui otomatis.");
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

    // Helper for Roman Numerals
    private function parseGradeLevel($kelas) {
        // 1. Try DB Column
        if (!empty($kelas->tingkat_kelas)) {
            return (int) $kelas->tingkat_kelas;
        }

        // 2. Try Standard Number Extract (e.g. "9A" -> 9)
        $num = (int) filter_var($kelas->nama_kelas, FILTER_SANITIZE_NUMBER_INT);
        if ($num > 0) return $num;

        // 3. Try Roman Numerals (Common in MTS)
        $romans = [
            'XII' => 12, 'XI' => 11, 'X' => 10,
            'IX' => 9, 'VIII' => 8, 'VII' => 7,
            'VI' => 6, 'V' => 5, 'IV' => 4,
            'III' => 3, 'II' => 2, 'I' => 1
        ];

        // Clean Name: Remove "KELAS" word if present
        $cleanName = trim(str_replace(['KELAS', 'Kelas', 'kelas'], '', $kelas->nama_kelas));
        $upperName = strtoupper($cleanName);
        
        foreach ($romans as $key => $val) {
            // Check if name START with Roman (e.g. "IX A")
            // Use word boundary check to avoid partial matches (e.g. "VI" in "DAVID")
            // But usually class names are simple.
            if (str_starts_with($upperName, $key . ' ') || $upperName === $key) {
                return $val;
            }
        }

        return 0; // Unknown
    }
}
