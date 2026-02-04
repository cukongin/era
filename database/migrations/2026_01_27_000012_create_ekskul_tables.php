<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Master Data Ekskul (Pramuka, PMR, Futsal, etc.)
        Schema::create('ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ekskul', 50);
            $table->enum('jenis', ['wajib', 'pilihan'])->default('pilihan');
            $table->timestamps();
        });

        // 2. Nilai Ekskul Siswa per Periode
        Schema::create('nilai_ekskul', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade'); // Kelas saat nilai diberikan
            $table->foreignId('id_periode')->constrained('periode')->onDelete('cascade');
            $table->foreignId('id_ekskul')->constrained('ekstrakurikuler')->onDelete('cascade');
            
            $table->string('predikat', 1); // A, B, C, D
            $table->string('keterangan')->nullable(); // "Sangat Baik", "Rajin", dll
            
            $table->timestamps();

            // Unik: Satu siswa hanya punya satu nilai untuk satu ekskul di periode tertentu
            $table->unique(['id_siswa', 'id_periode', 'id_ekskul']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('nilai_ekskul');
        Schema::dropIfExists('ekstrakurikuler');
    }
};
