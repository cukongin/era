<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Absensi (Sakit, Izin, Alpa) per Periode
        Schema::create('riwayat_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('id_periode')->constrained('periode')->onDelete('cascade');
            
            $table->integer('sakit')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('alpa')->default(0);
            
            $table->timestamps();
            
            // Unik per siswa per periode
            $table->unique(['id_siswa', 'id_periode']);
        });

        // 2. Catatan Wali Kelas (Sikap, Motivasi, dll)
        Schema::create('catatan_wali_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('id_periode')->constrained('periode')->onDelete('cascade');
            
            $table->text('catatan')->nullable(); // "Pertahankan prestasimu!", "Kurangi bolos", dll
            $table->enum('status_kenaikan', ['naik', 'tinggal', 'lulus', 'tidak_lulus'])->nullable(); // Akhir tahun
            
            $table->timestamps();

            // Unik per siswa per periode
            $table->unique(['id_siswa', 'id_periode']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('catatan_wali_kelas');
        Schema::dropIfExists('riwayat_absensi');
    }
};
