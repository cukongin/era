<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grading_formulas', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Rumus Standar 2024"
            $table->string('context'); // 'rapor_mi', 'rapor_mts', 'ijazah_mi', 'ijazah_mts'
            $table->text('formula'); // e.g. "([Rata_PH] * 0.6) + ([Nilai_PAS] * 0.4)"
            $table->boolean('is_active')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            // Ensure only one formula is active per context? 
            // We can strictly enforce this in application logic or Trigger, 
            // but for now index context for speed.
            $table->index('context');
        });
    }

    public function down()
    {
        Schema::dropIfExists('grading_formulas');
    }
};
