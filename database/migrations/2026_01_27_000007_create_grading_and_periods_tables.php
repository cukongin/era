<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Periode (Waktu Penilaian)
        Schema::create('periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajaran')->onDelete('cascade');
            $table->string('nama_periode'); // Cawu 1, Semester Ganjil
            $table->enum('tipe', ['CAWU', 'SEMESTER']);
            $table->enum('status', ['aktif', 'tutup'])->default('tutup');
            $table->enum('lingkup_jenjang', ['MI', 'MTS'])->nullable(); // MI uses Cawu, MTs uses Semester
            $table->timestamps();
        });

        // 2. KKM Mapel (Per Tahun, Per Jenjang/Tingkat)
        Schema::create('kkm_mapel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_mapel')->constrained('mapel')->onDelete('cascade');
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajaran')->onDelete('cascade');
            $table->enum('jenjang_target', ['MI', 'MTS']);
            $table->integer('nilai_kkm')->default(70);
            $table->timestamps();
        });

        // 3. Bobot Penilaian (Per Tahun, Per Jenjang)
        Schema::create('bobot_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajaran')->onDelete('cascade');
            $table->enum('jenjang', ['MI', 'MTS']);
            $table->integer('bobot_harian')->default(0); // %
            $table->integer('bobot_uts_cawu')->default(0); // % (PTS / Ujian Cawu)
            $table->integer('bobot_uas')->default(0); // % (PAS / PAT / Empty for MI if unused)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bobot_penilaian');
        Schema::dropIfExists('kkm_mapel');
        Schema::dropIfExists('periode');
    }
};
