<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nilai_siswa', function (Blueprint $table) {
            $table->id();
            
            // Core Relationships
            $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('id_kelas')->constrained('kelas'); // Historical record (where student was when graded)
            $table->foreignId('id_mapel')->constrained('mapel');
            $table->foreignId('id_periode')->constrained('periode');
            $table->foreignId('id_guru')->nullable()->constrained('users'); // Who inputted the grade
            
            // Grade Values
            $table->decimal('nilai_harian', 5, 2)->default(0);
            $table->decimal('nilai_uts_cawu', 5, 2)->default(0); // PTS or Ujian Cawu
            $table->decimal('nilai_uas', 5, 2)->default(0);      // PAS or PAT (0 for MI default)
            
            // Calculated Result
            $table->decimal('nilai_akhir', 5, 2)->default(0);
            $table->char('predikat', 1)->nullable(); // A, B, C, D
            $table->text('catatan')->nullable(); // Optional teacher notes

            $table->timestamps();

            // Unique constraint to prevent duplicate grading for same student/mapel/period
            $table->unique(['id_siswa', 'id_mapel', 'id_periode'], 'nilai_unique_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('nilai_siswa');
    }
};
