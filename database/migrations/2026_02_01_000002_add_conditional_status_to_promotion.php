<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add 'conditional' to ENUMs
        DB::statement("ALTER TABLE promotion_decisions MODIFY COLUMN final_decision ENUM('promoted', 'retained', 'pending', 'graduated', 'not_graduated', 'conditional') DEFAULT 'pending'");
        // Also update system_recommendation just in case we want to recommend it automatically later (though usually system is binary)
        DB::statement("ALTER TABLE promotion_decisions MODIFY COLUMN system_recommendation ENUM('promoted', 'retained', 'graduated', 'not_graduated', 'conditional')");
    }

    public function down()
    {
        // Revert 
        DB::statement("ALTER TABLE promotion_decisions MODIFY COLUMN final_decision ENUM('promoted', 'retained', 'pending', 'graduated', 'not_graduated') DEFAULT 'pending'");
        DB::statement("ALTER TABLE promotion_decisions MODIFY COLUMN system_recommendation ENUM('promoted', 'retained', 'graduated', 'not_graduated')");
    }
};
