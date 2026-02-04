<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Clean Duplicates in Bobot Penilaian
        // Find duplicates
        $duplicates = DB::table('bobot_penilaian')
            ->select('id_tahun_ajaran', 'jenjang', DB::raw('count(*) as total'))
            ->groupBy('id_tahun_ajaran', 'jenjang')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicates as $d) {
            // Keep the one with highest ID (latest) or highest values?
            // Let's keep the one with most specific data (sum of weights > 0)
            
            $records = DB::table('bobot_penilaian')
                ->where('id_tahun_ajaran', $d->id_tahun_ajaran)
                ->where('jenjang', $d->jenjang)
                ->orderBy('id', 'desc')
                ->get();

            $base = $records->shift(); // Keep this one (latest)
            
            // Delete the rest
            foreach ($records as $r) {
                DB::table('bobot_penilaian')->where('id', $r->id)->delete();
            }
        }

        // 2. Add Unique Constraint
        Schema::table('bobot_penilaian', function (Blueprint $table) {
            $table->unique(['id_tahun_ajaran', 'jenjang'], 'unique_bobot_tahun_jenjang');
        });

        // 3. Clean Duplicates in KKM Mapel?
        // Let's do similar for KKM just in case
         $kkmDupes = DB::table('kkm_mapel')
            ->select('id_tahun_ajaran', 'id_mapel', 'jenjang_target', DB::raw('count(*) as total'))
            ->groupBy('id_tahun_ajaran', 'id_mapel', 'jenjang_target')
            ->having('total', '>', 1)
            ->get();
            
        foreach ($kkmDupes as $d) {
            $records = DB::table('kkm_mapel')
                ->where('id_tahun_ajaran', $d->id_tahun_ajaran)
                ->where('id_mapel', $d->id_mapel)
                ->where('jenjang_target', $d->jenjang_target)
                ->orderBy('id', 'desc')
                ->get();
                
            $base = $records->shift();
            foreach ($records as $r) {
                 DB::table('kkm_mapel')->where('id', $r->id)->delete();
            }
        }

        Schema::table('kkm_mapel', function (Blueprint $table) {
             $table->unique(['id_tahun_ajaran', 'id_mapel', 'jenjang_target'], 'unique_kkm_mapel_tahun');
        });
    }

    public function down()
    {
        Schema::table('bobot_penilaian', function (Blueprint $table) {
            $table->dropUnique('unique_bobot_tahun_jenjang');
        });
        Schema::table('kkm_mapel', function (Blueprint $table) {
             $table->dropUnique('unique_kkm_mapel_tahun');
        });
    }
};
