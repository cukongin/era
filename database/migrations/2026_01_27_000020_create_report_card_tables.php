<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Ekstrakurikuler
        if (!Schema::hasTable('ekstrakurikuler')) {
            Schema::create('ekstrakurikuler', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
                $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade'); // Snapshot of class at that time
                $table->foreignId('id_periode')->constrained('periode')->onDelete('cascade');
                $table->string('nama_ekskul');
                $table->string('nilai')->nullable(); // A, B, C or 80, 90
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // 2. Absensi (Kehadiran)
        if (!Schema::hasTable('catatan_kehadiran')) {
            Schema::create('catatan_kehadiran', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
                $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade');
                $table->foreignId('id_periode')->constrained('periode')->onDelete('cascade');
                $table->integer('sakit')->default(0);
                $table->integer('izin')->default(0);
                $table->integer('tanpa_keterangan')->default(0);
                $table->timestamps();
            });
        }

        // 3. Catatan Wali Kelas
        if (!Schema::hasTable('catatan_wali_kelas')) {
            Schema::create('catatan_wali_kelas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
                $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade');
                $table->foreignId('id_periode')->constrained('periode')->onDelete('cascade');
                $table->text('catatan_akademik')->nullable(); // Prestasi/Kelulusan
                $table->text('catatan_karakter')->nullable(); // Sikap/Spiritual
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('catatan_wali_kelas');
        Schema::dropIfExists('catatan_kehadiran');
        Schema::dropIfExists('ekstrakurikuler');
    }
};
