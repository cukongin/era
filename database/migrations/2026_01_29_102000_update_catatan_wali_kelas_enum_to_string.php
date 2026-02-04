<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Change status_kenaikan to string (VARCHAR) to accommodate 'naik_percobaan' and future flexibility
        DB::statement("ALTER TABLE catatan_wali_kelas MODIFY COLUMN status_kenaikan VARCHAR(50) DEFAULT NULL");
    }

    public function down()
    {
        // Revert to enum (be careful of truncation if reverting)
        DB::statement("ALTER TABLE catatan_wali_kelas MODIFY COLUMN status_kenaikan ENUM('naik','tinggal','lulus','tidak_lulus') DEFAULT NULL");
    }
};
