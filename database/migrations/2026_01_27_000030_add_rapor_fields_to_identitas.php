<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('identitas_sekolah', function (Blueprint $table) {
            $table->string('tempat_titimangsa')->nullable()->after('alamat'); // e.g. "Jakarta"
            $table->date('tanggal_rapor')->nullable()->after('tempat_titimangsa'); // e.g. 2025-12-20
        });
    }

    public function down()
    {
        Schema::table('identitas_sekolah', function (Blueprint $table) {
            $table->dropColumn(['tempat_titimangsa', 'tanggal_rapor']);
        });
    }
};
