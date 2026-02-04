<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeKategoriColumnTypeInMapelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use Raw SQL because doctrine/dbal might not be installed
        DB::statement("ALTER TABLE mapel MODIFY kategori VARCHAR(100) NOT NULL DEFAULT 'UMUM'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to ENUM (Adjust values if needed, or keep as varchar)
        DB::statement("ALTER TABLE mapel MODIFY kategori ENUM('UMUM','AGAMA','MULOK') NOT NULL DEFAULT 'UMUM'");
    }
}
