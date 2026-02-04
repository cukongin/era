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
        if (!Schema::hasTable('grading_whitelist')) {
            Schema::create('grading_whitelist', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_guru')->constrained('users')->onDelete('cascade');
                $table->foreignId('id_periode')->constrained('periode')->onDelete('cascade');
                $table->text('alasan');
                $table->timestamp('valid_until')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_whitelist');
    }
};
