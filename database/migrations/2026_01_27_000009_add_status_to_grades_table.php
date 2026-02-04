<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('nilai_siswa', function (Blueprint $table) {
            $table->enum('status', ['draft', 'final'])->default('draft')->after('catatan');
        });
    }

    public function down()
    {
        Schema::table('nilai_siswa', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
