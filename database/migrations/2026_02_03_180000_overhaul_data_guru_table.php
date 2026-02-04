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
            // Drop old columns
            if (Schema::hasColumn('data_guru', 'nip')) {
                $table->dropColumn('nip');
            }
            if (Schema::hasColumn('data_guru', 'nuptk')) {
                $table->dropColumn('nuptk');
            }

            // Add new schema columns
            if (!Schema::hasColumn('data_guru', 'nik')) {
                $table->string('nik', 30)->nullable()->after('id_user');
            }
            if (!Schema::hasColumn('data_guru', 'npwp')) {
                $table->string('npwp', 30)->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('data_guru', 'pendidikan_terakhir')) {
                $table->string('pendidikan_terakhir')->nullable()->after('npwp');
            }
            if (!Schema::hasColumn('data_guru', 'riwayat_pesantren')) {
                $table->text('riwayat_pesantren')->nullable()->after('pendidikan_terakhir');
            }
            if (!Schema::hasColumn('data_guru', 'mapel_ajar_text')) {
                $table->string('mapel_ajar_text')->nullable()->after('riwayat_pesantren');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_guru', function (Blueprint $table) {
            $table->string('nip', 30)->nullable();
            $table->string('nuptk', 30)->nullable();
            
            $table->dropColumn(['nik', 'npwp', 'pendidikan_terakhir', 'riwayat_pesantren', 'mapel_ajar_text']);
        });
    }
};
