<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('riwayat_perubahan_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nilai_siswa')->constrained('nilai_siswa')->onDelete('cascade');
            $table->foreignId('id_user')->constrained('users'); // Who modified
            $table->json('data_lama')->nullable(); // Previous values
            $table->json('data_baru')->nullable(); // New values
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('riwayat_perubahan_nilai');
    }
};
