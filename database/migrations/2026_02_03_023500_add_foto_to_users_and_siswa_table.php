<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('data_guru', function (Blueprint $table) {
            if (!Schema::hasColumn('data_guru', 'foto')) {
                $table->string('foto')->nullable()->after('alamat');
            }
        });

        Schema::table('siswa', function (Blueprint $table) {
            if (!Schema::hasColumn('siswa', 'foto')) {
                $table->string('foto')->nullable()->after('alamat_lengkap');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_guru', function (Blueprint $table) {
            $table->dropColumn('foto');
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
