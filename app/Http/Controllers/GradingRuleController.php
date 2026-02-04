<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TahunAjaran;
use App\Models\BobotPenilaian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class GradingRuleController extends Controller
{
    public function index()
    {
        // SELF-HEALING: Ensure Table and Columns Exist
        if (Schema::hasTable('grading_settings')) {
            if (!Schema::hasColumn('grading_settings', 'effective_days_year')) {
                Schema::table('grading_settings', function (Blueprint $table) {
                    $table->integer('effective_days_year')->default(200);
                });
            }
        } else {
            // Create Table if completely missing (Panic Mode)
            Schema::create('grading_settings', function (Blueprint $table) {
                $table->id();
                $table->enum('jenjang', ['MI', 'MTS'])->unique();
                $table->integer('kkm_default')->default(70);
                $table->string('scale_type')->default('0-100');
                $table->boolean('rounding_enable')->default(true);
                $table->integer('promotion_max_kkm_failure')->default(3);
                $table->integer('promotion_min_attendance')->default(85);
                $table->integer('effective_days_year')->default(200);
                $table->string('promotion_min_attitude')->default('B');
                $table->timestamps();
            });
            // Seed
            DB::table('grading_settings')->insert([
                ['jenjang' => 'MI'], ['jenjang' => 'MTS']
            ]);
        }
        
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) return back()->with('error', 'Tahun Ajaran belum aktif.');

        return view('settings.grading_rules_v2', compact('activeYear'));
    }

    public function getRules($jenjang)
    {
        try {
            $jenjang = strtoupper($jenjang);
            $activeYear = TahunAjaran::where('status', 'aktif')->first();

            // 1. Predicates
            $predicatesQuery = DB::table('predikat_nilai')
                ->where('jenjang', $jenjang);
                
            // SELF-HEALING: Check for duplicates
            if ($predicatesQuery->count() > 4) {
                $all = $predicatesQuery->get();
                $grouped = $all->groupBy('grade');
                $idsToDelete = [];
                
                foreach ($grouped as $grade => $rows) {
                    if ($rows->count() > 1) {
                        // Keep the first one (usually smallest ID or specific preference)
                        $keep = $rows->first();
                        // Mark others for deletion
                        foreach ($rows as $r) {
                            if ($r->id != $keep->id) {
                                $idsToDelete[] = $r->id;
                            }
                        }
                    }
                }
                
                if (!empty($idsToDelete)) {
                    DB::table('predikat_nilai')->whereIn('id', $idsToDelete)->delete();
                }
            }

            $predicates = DB::table('predikat_nilai')
                ->where('jenjang', $jenjang)
                ->orderBy('min_score', 'desc')
                ->get();

            // 2. Settings (KKM, Scale, Promotion)
            $settings = DB::table('grading_settings')->where('jenjang', $jenjang)->first();

            // 3. Weights
            $weights = BobotPenilaian::where('id_tahun_ajaran', $activeYear->id)
                ->where('jenjang', $jenjang)
                ->first();
                
            return response()->json([
                'predicates' => $predicates,
                'settings' => $settings,
                'weights' => $weights
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], 500);
        }
    }

    public function updateAll(Request $request)
    {
        $jenjang = strtoupper($request->jenjang);
        $activeYear = TahunAjaran::where('status', 'aktif')->first();

        DB::beginTransaction();
        try {
            // 1. Update Predicates
            foreach ($request->predicates as $rule) {
                DB::table('predikat_nilai')
                    ->where('id', $rule['id'])
                    ->update([
                        'min_score' => $rule['min_score'],
                        'max_score' => $rule['max_score']
                    ]);
            }

            // 2. Update Settings
            // DEBUG: Check if row exists
            $existingRow = DB::table('grading_settings')->where('jenjang', $jenjang)->first();
            $rowExists = $existingRow ? 'YES' : 'NO';

            $affected = DB::table('grading_settings')->where('jenjang', $jenjang)->update([
                'kkm_default' => $request->settings['kkm_default'],
                'scale_type' => $request->settings['scale_type'],
                'rounding_enable' => $request->settings['rounding_enable'],
                'promotion_max_kkm_failure' => $request->settings['promotion_max_kkm_failure'],
                'promotion_min_attendance' => $request->settings['promotion_min_attendance'],
                'effective_days_year' => $request->settings['effective_days_year'] ?? 200,
                'promotion_min_attitude' => $request->settings['promotion_min_attitude'],
                'updated_at' => now()
            ]);

            // [NEW] KKM Default Propagation
            // Check if KKM changes? For now, we propagate every time user saves, to be safe.
            $newKkm = $request->settings['kkm_default'];
            $mapels = \App\Models\Mapel::whereIn('target_jenjang', [$jenjang, 'SEMUA'])->pluck('id');
            $countKkmUpdated = 0;
            
            foreach ($mapels as $mapelId) {
                 \App\Models\KkmMapel::updateOrCreate(
                    [
                        'id_tahun_ajaran' => $activeYear->id,
                        'id_mapel' => $mapelId,
                        'jenjang_target' => $jenjang
                    ],
                    ['nilai_kkm' => $newKkm]
                );
                $countKkmUpdated++;
            }

            // 3. Update Weights
            BobotPenilaian::updateOrCreate(
                ['id_tahun_ajaran' => $activeYear->id, 'jenjang' => $jenjang],
                [
                    'bobot_harian' => $request->weights['bobot_harian'],
                    'bobot_uts_cawu' => $request->weights['bobot_uts_cawu'],
                    'bobot_uas' => $request->weights['bobot_uas'],
                ]
            );
            
            DB::commit();

            
            $msg = "Aturan disimpan!";
            if($countKkmUpdated > 0) {
                $msg .= " KKM Mapel ikut diperbarui ($countKkmUpdated mapel).";
            }
            
            return response()->json([
                'message' => $msg,
                'debug' => [
                    'jenjang' => $jenjang,
                    'row_exists' => $rowExists,
                    'settings_update_affected' => $affected,
                    'received_effective_days' => $request->settings['effective_days_year'] ?? 'NULL',
                    'db_column_exists' => Schema::hasColumn('grading_settings', 'effective_days_year') ? 'YES' : 'NO'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
}
