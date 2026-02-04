<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grading_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('jenjang', ['MI', 'MTS'])->unique();
            $table->integer('kkm_default')->default(70);
            $table->string('scale_type')->default('0-100'); // 0-100 or 1-4
            $table->boolean('rounding_enable')->default(true);
            $table->integer('promotion_max_kkm_failure')->default(3);
            $table->integer('promotion_min_attendance')->default(85); // %
            $table->string('promotion_min_attitude')->default('B');
            $table->timestamps();
        });

        // Seed Defaults
        DB::table('grading_settings')->insert([
            [
                'jenjang' => 'MI', 
                'kkm_default' => 70, 
                'scale_type' => '0-100', 
                'rounding_enable' => true,
                'promotion_max_kkm_failure' => 3,
                'promotion_min_attendance' => 85,
                'promotion_min_attitude' => 'B'
            ],
            [
                'jenjang' => 'MTS', 
                'kkm_default' => 75, 
                'scale_type' => '0-100', 
                'rounding_enable' => true,
                'promotion_max_kkm_failure' => 3,
                'promotion_min_attendance' => 88,
                'promotion_min_attitude' => 'B'
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('grading_settings');
    }
};
