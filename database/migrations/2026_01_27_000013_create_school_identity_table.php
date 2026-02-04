<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('identitas_sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('jenjang', 10)->default('MI'); // MI/MTS
            $table->string('nama_sekolah')->default('Madrasah Hebat Bermartabat');
            $table->string('nsm')->nullable();
            $table->string('npsn')->nullable();
            $table->text('alamat')->nullable();
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten')->nullable(); // Kota/Kab
            $table->string('provinsi')->nullable();
            
            $table->string('no_telp')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            
            $table->string('kepala_madrasah')->nullable();
            $table->string('nip_kepala')->nullable();
            
            $table->string('logo')->nullable(); // Path to logo image
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('identitas_sekolah');
    }
};
