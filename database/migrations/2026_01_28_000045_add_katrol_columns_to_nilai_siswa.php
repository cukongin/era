<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('nilai_siswa', function (Blueprint $table) {
            $table->decimal('nilai_akhir_asli', 5, 2)->nullable()->after('nilai_akhir');
            $table->boolean('is_katrol')->default(false)->after('nilai_akhir_asli');
        });
    }

    public function down()
    {
        Schema::table('nilai_siswa', function (Blueprint $table) {
            $table->dropColumn(['nilai_akhir_asli', 'is_katrol']);
        });
    }
};
