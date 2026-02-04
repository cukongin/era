<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE nilai_siswa 
            MODIFY COLUMN nilai_harian DECIMAL(5,2) NULL DEFAULT NULL,
            MODIFY COLUMN nilai_uts_cawu DECIMAL(5,2) NULL DEFAULT NULL,
            MODIFY COLUMN nilai_uas DECIMAL(5,2) NULL DEFAULT NULL,
            MODIFY COLUMN nilai_akhir DECIMAL(5,2) NULL DEFAULT NULL,
            MODIFY COLUMN nilai_akhir_asli DECIMAL(5,2) NULL DEFAULT NULL
        ");
    }

    public function down()
    {
        // Revert to non-nullable default 0
        DB::statement("UPDATE nilai_siswa SET nilai_harian = 0 WHERE nilai_harian IS NULL");
        DB::statement("UPDATE nilai_siswa SET nilai_uts_cawu = 0 WHERE nilai_uts_cawu IS NULL");
        DB::statement("UPDATE nilai_siswa SET nilai_uas = 0 WHERE nilai_uas IS NULL");
        DB::statement("UPDATE nilai_siswa SET nilai_akhir = 0 WHERE nilai_akhir IS NULL");
        DB::statement("UPDATE nilai_siswa SET nilai_akhir_asli = 0 WHERE nilai_akhir_asli IS NULL");

        DB::statement("ALTER TABLE nilai_siswa 
            MODIFY COLUMN nilai_harian DECIMAL(5,2) NOT NULL DEFAULT 0,
            MODIFY COLUMN nilai_uts_cawu DECIMAL(5,2) NOT NULL DEFAULT 0,
            MODIFY COLUMN nilai_uas DECIMAL(5,2) NOT NULL DEFAULT 0,
            MODIFY COLUMN nilai_akhir DECIMAL(5,2) NOT NULL DEFAULT 0,
            MODIFY COLUMN nilai_akhir_asli DECIMAL(5,2) NOT NULL DEFAULT 0
        ");
    }
};
