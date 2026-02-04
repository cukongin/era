<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Altering ENUM in Laravel/MySQL requires raw statement usually, or repeated migration
        // Using raw statement for safety and speed on MySQL
        DB::statement("ALTER TABLE promotion_decisions MODIFY COLUMN final_decision ENUM('promoted', 'retained', 'pending', 'graduated', 'not_graduated') DEFAULT 'pending'");
        DB::statement("ALTER TABLE promotion_decisions MODIFY COLUMN system_recommendation ENUM('promoted', 'retained', 'graduated', 'not_graduated')");
    }

    public function down()
    {
        // Revert to original
        DB::statement("ALTER TABLE promotion_decisions MODIFY COLUMN final_decision ENUM('promoted', 'retained', 'pending') DEFAULT 'pending'");
        DB::statement("ALTER TABLE promotion_decisions MODIFY COLUMN system_recommendation ENUM('promoted', 'retained')");
    }
};
