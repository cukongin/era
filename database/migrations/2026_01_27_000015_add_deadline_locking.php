<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Add Deadline to Periode
        Schema::table('periode', function (Blueprint $table) {
            $table->dateTime('tenggat_waktu')->nullable()->after('status');
        });

        // 2. Grading Whitelist (Pengecualian)
        Schema::create('grading_whitelist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_guru')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_periode')->constrained('periode')->onDelete('cascade');
            $table->string('alasan')->nullable();
            $table->dateTime('valid_until')->nullable(); // Sampai kapan izin berlaku
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grading_whitelist');
        
        Schema::table('periode', function (Blueprint $table) {
            $table->dropColumn('tenggat_waktu');
        });
    }
};
