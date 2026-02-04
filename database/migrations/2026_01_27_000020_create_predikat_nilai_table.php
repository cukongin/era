<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('predikat_nilai', function (Blueprint $table) {
            $table->id();
            $table->enum('jenjang', ['MI', 'MTS']);
            $table->string('grade'); // A, B, C, D
            $table->integer('min_score');
            $table->integer('max_score');
            $table->string('deskripsi')->nullable(); // Sangat Baik, dll
            $table->timestamps();
        });

        // Seed Default Data
        $data = [
            // MI Defaults (Looser)
            ['jenjang' => 'MI', 'grade' => 'A', 'min_score' => 90, 'max_score' => 100, 'deskripsi' => 'Sangat Baik'],
            ['jenjang' => 'MI', 'grade' => 'B', 'min_score' => 80, 'max_score' => 89, 'deskripsi' => 'Baik'],
            ['jenjang' => 'MI', 'grade' => 'C', 'min_score' => 70, 'max_score' => 79, 'deskripsi' => 'Cukup'],
            ['jenjang' => 'MI', 'grade' => 'D', 'min_score' => 0,  'max_score' => 69, 'deskripsi' => 'Perlu Bimbingan'],

            // MTs Defaults (Stricter)
            ['jenjang' => 'MTS', 'grade' => 'A', 'min_score' => 92, 'max_score' => 100, 'deskripsi' => 'Sangat Baik'],
            ['jenjang' => 'MTS', 'grade' => 'B', 'min_score' => 83, 'max_score' => 91, 'deskripsi' => 'Baik'],
            ['jenjang' => 'MTS', 'grade' => 'C', 'min_score' => 75, 'max_score' => 82, 'deskripsi' => 'Cukup'],
            ['jenjang' => 'MTS', 'grade' => 'D', 'min_score' => 0,  'max_score' => 74, 'deskripsi' => 'Kurang'],
        ];

        DB::table('predikat_nilai')->insert($data);
    }

    public function down()
    {
        Schema::dropIfExists('predikat_nilai');
    }
};
