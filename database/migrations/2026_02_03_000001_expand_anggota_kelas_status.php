<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Expand the ENUM values for anggota_kelas status
        // We use raw SQL because modifying ENUMs in Laravel/Doctrine is tricky and sometimes requires strict syntax
        // This query works for MySQL/MariaDB
        DB::statement("ALTER TABLE anggota_kelas MODIFY COLUMN status ENUM('aktif', 'pindah', 'naik_kelas', 'tinggal_kelas', 'lulus', 'mutasi', 'keluar', 'non-aktif', 'meninggal') DEFAULT 'aktif'");
    }

    public function down()
    {
        // Revert (Optional, but usually we just leave expanded enums)
        // DB::statement("ALTER TABLE anggota_kelas MODIFY COLUMN status ENUM('aktif', 'pindah', 'naik_kelas') DEFAULT 'aktif'");
    }
};
