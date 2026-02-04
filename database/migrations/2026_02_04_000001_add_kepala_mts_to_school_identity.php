<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('identitas_sekolah', function (Blueprint $table) {
            $table->string('kepala_madrasah_mts')->nullable()->after('kepala_madrasah');
            $table->string('nip_kepala_mts')->nullable()->after('nip_kepala');
        });
    }

    public function down()
    {
        Schema::table('identitas_sekolah', function (Blueprint $table) {
            $table->dropColumn(['kepala_madrasah_mts', 'nip_kepala_mts']);
        });
    }
};
