<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTahunAjaranToPredikatNilaiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('predikat_nilai', function (Blueprint $table) {
            $table->foreignId('id_tahun_ajaran')->nullable()->constrained('tahun_ajaran')->onDelete('cascade');
        });

        // Seed existing with active year
        $activeYearId = \DB::table('tahun_ajaran')->where('status', 'aktif')->value('id');
        if ($activeYearId) {
            \DB::table('predikat_nilai')->update(['id_tahun_ajaran' => $activeYearId]);
        }
    }

    public function down()
    {
        Schema::table('predikat_nilai', function (Blueprint $table) {
            $table->dropForeign(['id_tahun_ajaran']);
            $table->dropColumn('id_tahun_ajaran');
        });
    }
}
