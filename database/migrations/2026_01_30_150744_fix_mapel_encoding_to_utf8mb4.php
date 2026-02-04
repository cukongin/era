<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixMapelEncodingToUtf8mb4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Force Table Conversion
        DB::statement("ALTER TABLE mapel CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Ensure specific columns are also modified (Double check)
        DB::statement("ALTER TABLE mapel MODIFY nama_mapel VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        DB::statement("ALTER TABLE mapel MODIFY nama_kitab VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
    }

    public function down()
    {
        // No down needed really, utf8mb4 is standard
    }
}
