<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('global_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'allow_teacher_input'
            $table->text('value')->nullable(); // e.g., '1' or '0'
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        DB::table('global_settings')->insert([
            ['key' => 'access_guru_input_nilai', 'value' => '1', 'description' => 'Izinkan Guru Input Nilai'],
            ['key' => 'access_guru_input_soal', 'value' => '1', 'description' => 'Izinkan Guru Input Bank Soal'],
            ['key' => 'access_wali_cetak_rapor', 'value' => '1', 'description' => 'Izinkan Wali Kelas Cetak Rapor'],
            ['key' => 'access_wali_edit_siswa', 'value' => '0', 'description' => 'Izinkan Wali Kelas Edit Data Siswa'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('global_settings');
    }
}
