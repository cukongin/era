<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('catatan_kehadiran', function (Blueprint $table) {
            $table->string('kelakuan')->default('Baik')->after('tanpa_keterangan');
            $table->string('kerajinan')->default('Baik')->after('kelakuan');
            $table->string('kebersihan')->default('Baik')->after('kerajinan');
        });
    }

    public function down()
    {
        Schema::table('catatan_kehadiran', function (Blueprint $table) {
            $table->dropColumn(['kelakuan', 'kerajinan', 'kebersihan']);
        });
    }
};
