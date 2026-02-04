<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE riwayat_pendidikan_guru MODIFY tahun_masuk VARCHAR(100)');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE riwayat_pendidikan_guru MODIFY tahun_lulus VARCHAR(100)');
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE riwayat_pendidikan_guru MODIFY tahun_masuk VARCHAR(4)');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE riwayat_pendidikan_guru MODIFY tahun_lulus VARCHAR(4)');
    }
};
