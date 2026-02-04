<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promotion_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('id_kelas')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajaran')->cascadeOnDelete();
            
            // Logic metrics
            $table->float('average_score')->default(0);
            $table->integer('kkm_failure_count')->default(0);
            $table->float('attendance_percent')->default(0);
            $table->char('attitude_grade', 1)->nullable(); // A, B, C, D
            
            // Decisions
            $table->enum('system_recommendation', ['promoted', 'retained']); // Murni hasil hitungan
            $table->enum('final_decision', ['promoted', 'retained', 'pending'])->default('pending'); // Keputusan akhir (bisa override)
            
            $table->text('notes')->nullable(); // Alasan otomatis atau manual
            $table->unsignedBigInteger('override_by')->nullable(); // Jika di-override guru
            $table->timestamp('decided_at')->nullable();
            
            $table->timestamps();
            
            // Unique constraint: Satu siswa hanya punya 1 keputusan per kelas per tahun
            $table->unique(['id_siswa', 'id_kelas', 'id_tahun_ajaran']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotion_decisions');
    }
};
