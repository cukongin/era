<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\NilaiSiswa;
use App\Models\TahunAjaran;
use App\Models\GradingSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GradeAdjustmentController extends Controller
{
    public function index(Request $request) 
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // Ensure Wali Kelas specific access or Admin
        // For now, accept kelas_id
        $kelasId = $request->kelas_id;
        $mapelId = $request->mapel_id; 
        // Initialize Query
        $classesQuery = Kelas::where('id_tahun_ajaran', $activeYear->id)->orderBy('nama_kelas');

        // Restrict to logged-in user if not admin (Wali Kelas context)
        $user = Auth::user();
        if (!$user->isAdmin()) {
            $classesQuery->where('id_wali_kelas', $user->id);
        }

        $classes = $classesQuery->get();

        if (!$kelasId && $classes->count() > 0) $kelasId = $classes->first()->id;

        $subjects = DB::table('mapel')->orderBy('nama_mapel')->get();
        if (!$mapelId && $subjects->count() > 0) $mapelId = $subjects->first()->id;

        // Define Jenjang EARLY
        $kelas = Kelas::find($kelasId);
        $jenjang = $kelas ? strtoupper($kelas->jenjang->kode) : 'MI';
        $settings = DB::table('grading_settings')->where('jenjang', $jenjang)->first();

        // Fetch Grades by Periods
        // 1. Get ALL Periods for this Year + Jenjang (for Dropdown)
        $allPeriods = \App\Models\Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('lingkup_jenjang', $jenjang)
            ->get();
            
        // 2. Determine Selected Period (Default to Active, or First available)
        $selectedPeriodeId = $request->periode_id;
        $activePeriod = $allPeriods->where('status', 'aktif')->first();
        
        if (!$selectedPeriodeId) {
            $selectedPeriodeId = $activePeriod ? $activePeriod->id : ($allPeriods->first()->id ?? null);
        }

        $grades = NilaiSiswa::with('siswa')
            ->where('id_kelas', $kelasId)
            ->where('id_mapel', $mapelId)
            ->where('id_periode', $selectedPeriodeId)
            ->get();
            
        // KKM
        $defaultKkm = $settings->kkm_default ?? 70;
        
        // Mapel specific KKM
        $kkmMapel = \App\Models\KkmMapel::where('id_mapel', $mapelId)
            ->where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang_target', $jenjang)
            ->value('nilai_kkm');
        
        $currentKkm = $kkmMapel ?? $defaultKkm;

        return view('wali-kelas.katrol.index', compact('classes', 'subjects', 'grades', 'kelasId', 'mapelId', 'currentKkm', 'allPeriods', 'selectedPeriodeId'));
    }

    public function store(Request $request)
    {
        // Bulk Action
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        $method = $request->method_type; // 'kkm', 'points'
        
        $kelasId = $request->kelas_id;
        $mapelId = $request->mapel_id;
        
        $kelas = Kelas::findOrFail($kelasId);
        $jenjang = strtoupper($kelas->jenjang->kode);

        // Get active periods
        // FIXED: Use specific period from request if valid, otherwise fallback to active
        $targetPeriodId = $request->periode_id;
        
        // Validation: Verify period belongs to year/jenjang? 
        // For now, trusting the ID is sufficient as we filter by class/mapel/period combo.

        $grades = NilaiSiswa::where('id_kelas', $kelasId)
            ->where('id_mapel', $mapelId)
            ->where('id_periode', $targetPeriodId)
            ->get();
        $kkmVal = DB::table('kkm_mapel')->where('id_mapel', $mapelId)
            ->where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang_target', $jenjang)
            ->value('nilai_kkm');
        if (!$kkmVal) {
             $st = DB::table('grading_settings')->where('jenjang', $jenjang)->first();
             $kkmVal = $st->kkm_default ?? 70;
        }

        DB::beginTransaction();
        try {
            foreach ($grades as $grade) {
                $originalScore = $grade->nilai_akhir_asli ?? $grade->nilai_akhir;
                // If nilai_akhir_asli is currently null, it means no katrol happened yet.
                // We should set it to nilai_akhir before modifying it.
                if (is_null($grade->nilai_akhir_asli)) {
                    $grade->nilai_akhir_asli = $grade->nilai_akhir;
                    $originalScore = $grade->nilai_akhir;
                }

                $newScore = $grade->nilai_akhir;
                $isKatrol = false;

                $note = null;

                if ($method === 'kkm') {
                    // Method 1: Tuntaskan ke KKM
                    if ($originalScore < $kkmVal) {
                        $newScore = $kkmVal;
                        $isKatrol = true;
                        $note = "KKM ($kkmVal)";
                        
                        // Log
                        // Log::info("Katrol KKM: {$grade->siswa->nama_lengkap} $originalScore -> $newScore");
                    }
                } elseif ($method === 'points') {
                    // Method 2 & 3: Tambah Poin with Ceiling
                    $points = (float) $request->boost_points;
                    $ceiling = (float) $request->max_ceiling;
                    
                    if ($originalScore < $ceiling) {
                        $potential = $originalScore + $points;
                        $newScore = min($potential, $ceiling);
                        $isKatrol = true;
                        $note = "Poin (+$points, Max $ceiling)";
                    } else {
                        // Above ceiling, no change (or revert to original)
                        $newScore = $originalScore;
                    }
                } elseif ($method === 'percentage') {
                    // Method 3: Percentage Boost
                    $percent = (float) $request->boost_percent; // e.g. 10
                    $factor = 1 + ($percent / 100);
                    $potential = $originalScore * $factor;
                    $newScore = min(100, round($potential));
                    
                    // Only mark as katrol if it actually changed logic? 
                    // Usually always true unless percent is 0.
                    $isKatrol = $newScore > $originalScore;
                    if ($isKatrol) $note = "Persen ($percent%)";
                    
                } elseif ($method === 'linear_scale') {
                    // Method 4: Linear Interpolation (Scaling)

                    $targetMin = (float) $request->target_min;
                    $targetMax = (float) $request->target_max;

                    // Prefer User Input for Data Min/Max (Custom Range)
                    // If not provided, fallback to actual data min/max
                    if ($request->has('data_min') && $request->filled('data_min')) {
                        $dataMin = (float) $request->data_min;
                    } else {
                        $dataMin = $grades->min(fn($g) => $g->nilai_akhir_asli ?? $g->nilai_akhir);
                    }

                    if ($request->has('data_max') && $request->filled('data_max')) {
                        $dataMax = (float) $request->data_max;
                    } else {
                        $dataMax = $grades->max(fn($g) => $g->nilai_akhir_asli ?? $g->nilai_akhir);
                    }
                    
                    if ($dataMax == $dataMin) {
                         $newScore = $targetMax; // Flat map if no variance
                    } else {
                        $ratio = ($originalScore - $dataMin) / ($dataMax - $dataMin);
                        $range = $targetMax - $targetMin;
                        $final = $targetMin + ($ratio * $range);
                        $newScore = min(100, round($final));
                    }
                    
                    // Prevent Downgrade: Keep original if new score is lower
                    if ($newScore < $originalScore) {
                        $newScore = $originalScore;
                    }

                    $isKatrol = $newScore != $originalScore; // Could be up or down (now only up)
                    if ($isKatrol) $note = "Interpolasi ($targetMin-$targetMax)";
                    
                } elseif ($method === 'reset') {
                     $newScore = $originalScore;
                     $isKatrol = false;
                     $note = null;
                }

                $grade->nilai_akhir = $newScore;
                $grade->nilai_akhir_asli = $originalScore; // Ensure this is saved
                $grade->is_katrol = $isKatrol;
                $grade->katrol_note = $note;
                
                // Recalculate Predicate
                $rules = DB::table('predikat_nilai')->where('jenjang', $jenjang)->orderBy('min_score', 'desc')->get();
                $predikat = 'D';
                foreach ($rules as $rule) {
                    if ($newScore >= $rule->min_score) {
                        $predikat = $rule->grade;
                        break;
                    }
                }
                $grade->predikat = $predikat;
                $grade->save();
            }
            
            DB::commit();
            return back()->with('success', 'Katrol Nilai Berhasil diterapkan!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}
