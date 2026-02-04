<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNilaiIjazahTable extends Migration
{
    public function up()
    {
        Schema::create('nilai_ijazah', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_siswa');
            $table->unsignedBigInteger('id_mapel');
            
            // Rata-Rata Rapor (Sem 1-5 / Kelas 1-5). 
            // Nullable because it might be auto-calculated OR manual.
            $table->decimal('rata_rata_rapor', 5, 2)->nullable();
            
            // Ujian Madrasah (UM)
            $table->decimal('nilai_ujian_madrasah', 5, 2)->nullable();
            
            // Final Ijazah Grade (Formula: 60% RR + 40% UM, or similar)
            $table->decimal('nilai_ijazah', 5, 2)->nullable();
            
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate entries for same student-mapel
            $table->unique(['id_siswa', 'id_mapel']);
            
            // FKs if possible, or just raw IDs if speed is concern (but FK better for integrity)
            $table->foreign('id_siswa')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('id_mapel')->references('id')->on('mapel')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('nilai_ijazah');
    }
}
