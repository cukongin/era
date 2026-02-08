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
        Schema::table('grading_settings', function (Blueprint $table) {
            // Safe add column: promotion_requires_all_periods
            if (!Schema::hasColumn('grading_settings', 'promotion_requires_all_periods')) {
                $table->boolean('promotion_requires_all_periods')->default(true)->after('promotion_min_attitude');
            }
            
            // Safe add column: effective_days_year
            if (!Schema::hasColumn('grading_settings', 'effective_days_year')) {
                $table->integer('effective_days_year')->default(220)->after('promotion_min_attendance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grading_settings', function (Blueprint $table) {
            if (Schema::hasColumn('grading_settings', 'promotion_requires_all_periods')) {
                $table->dropColumn('promotion_requires_all_periods');
            }
            if (Schema::hasColumn('grading_settings', 'effective_days_year')) {
                $table->dropColumn('effective_days_year');
            }
        });
    }
};
