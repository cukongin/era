<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('catatan_wali_kelas')) {
            Schema::table('catatan_wali_kelas', function (Blueprint $table) {
                if (!Schema::hasColumn('catatan_wali_kelas', 'status_kenaikan')) {
                    $table->string('status_kenaikan')->nullable()->after('catatan_karakter');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('catatan_wali_kelas')) {
            Schema::table('catatan_wali_kelas', function (Blueprint $table) {
                if (Schema::hasColumn('catatan_wali_kelas', 'status_kenaikan')) {
                    $table->dropColumn('status_kenaikan');
                }
            });
        }
    }
};
