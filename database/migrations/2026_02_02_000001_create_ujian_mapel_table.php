<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ujian_mapel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajaran')->onDelete('cascade');
            $table->enum('jenjang', ['MI', 'MTS']);
            $table->foreignId('id_mapel')->constrained('mapel')->onDelete('cascade');
            $table->timestamps();
            
            // Prevent duplicate mapel per year+jenjang
            $table->unique(['id_tahun_ajaran', 'jenjang', 'id_mapel']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ujian_mapel');
    }
};
