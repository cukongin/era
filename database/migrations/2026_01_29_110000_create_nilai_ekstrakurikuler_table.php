<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nilai_ekstrakurikuler')) {
            Schema::create('nilai_ekstrakurikuler', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
                $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade');
                $table->foreignId('id_periode')->constrained('periode')->onDelete('cascade');
                // We'll store ID Ekskul if referring to Master, OR just store Name for flexibility (since user code had strings)
                // Let's store BOTH or just Name if user wants free text, but usually ID is better.
                // Given the user code `nama_ekskul`, I'll use `nama_ekskul` string for now to match the view's expectation rapidly.
                // Later user can link to ID if they want restrict options.
                $table->string('nama_ekskul'); 
                $table->string('nilai')->nullable(); // A, B, C
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('nilai_ekstrakurikuler');
    }
};
