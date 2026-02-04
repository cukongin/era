<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mapel', function (Blueprint $table) {
            $table->enum('target_jenjang', ['MI', 'MTS', 'SEMUA'])->default('SEMUA')->after('kategori');
        });
    }

    public function down()
    {
        Schema::table('mapel', function (Blueprint $table) {
            $table->dropColumn('target_jenjang');
        });
    }
};
