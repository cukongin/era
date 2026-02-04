<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_pendidikan_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_guru_id')->constrained('data_guru')->onDelete('cascade');
            $table->string('jenjang')->nullable(); // SD, SMP, SMA, S1, S2, Pesantren
            $table->string('nama_instansi');
            $table->string('tahun_masuk', 4)->nullable();
            $table->string('tahun_lulus', 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pendidikan_guru');
    }
};
