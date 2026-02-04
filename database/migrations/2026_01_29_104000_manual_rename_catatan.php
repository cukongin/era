<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Use raw SQL to rename column without doctrine/dbal dependency
        // CHANGE old_name new_name TYPE
        DB::statement("ALTER TABLE catatan_wali_kelas CHANGE catatan catatan_akademik TEXT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE catatan_wali_kelas CHANGE catatan_akademik catatan TEXT NULL");
    }
};
