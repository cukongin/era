<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeWeightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get Scheme IDs
        $cawu = DB::table('academic_schemes')->where('code', 'CAWU')->first();
        $semester = DB::table('academic_schemes')->where('code', 'SEMESTER')->first();

        if ($cawu) {
            DB::table('grade_weights')->insert([
                [
                    'academic_scheme_id' => $cawu->id,
                    'name' => 'Harian',
                    'category' => 'DAILY',
                    'percentage' => 50,
                    'created_at' => now(), 'updated_at' => now()
                ],
                [
                    'academic_scheme_id' => $cawu->id,
                    'name' => 'Ujian',
                    'category' => 'FINAL',
                    'percentage' => 50,
                    'created_at' => now(), 'updated_at' => now()
                ]
            ]);
        }

        if ($semester) {
            DB::table('grade_weights')->insert([
                [
                    'academic_scheme_id' => $semester->id,
                    'name' => 'Harian',
                    'category' => 'DAILY',
                    'percentage' => 40,
                    'created_at' => now(), 'updated_at' => now()
                ],
                [
                    'academic_scheme_id' => $semester->id,
                    'name' => 'PTS',
                    'category' => 'MID',
                    'percentage' => 30,
                    'created_at' => now(), 'updated_at' => now()
                ],
                [
                    'academic_scheme_id' => $semester->id,
                    'name' => 'PAS',
                    'category' => 'FINAL',
                    'percentage' => 30,
                    'created_at' => now(), 'updated_at' => now()
                ]
            ]);
        }
    }
}
