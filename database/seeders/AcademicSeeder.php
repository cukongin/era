<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create Academic Year (2024/2025)
        $yearId = DB::table('academic_years')->insertGetId([
            'name' => '2024/2025',
            'status' => 'active',
            'start_date' => '2024-07-15',
            'end_date' => '2025-06-20',
            'created_at' => now(), 'updated_at' => now()
        ]);

        // Get Scheme IDs
        $semSchemeId = DB::table('academic_schemes')->where('code', 'SEMESTER')->value('id');
        $cawuSchemeId = DB::table('academic_schemes')->where('code', 'CAWU')->value('id');

        // 2. Create Terms for MTs (Semester)
        DB::table('academic_terms')->insert([
            [
                'academic_year_id' => $yearId,
                'academic_scheme_id' => $semSchemeId,
                'name' => 'Semester Ganjil',
                'sequence' => 1,
                'is_active' => true, // Semester 1 Active
                'is_grading_open' => true, // Grading open
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'academic_year_id' => $yearId,
                'academic_scheme_id' => $semSchemeId,
                'name' => 'Semester Genap',
                'sequence' => 2,
                'is_active' => false,
                'is_grading_open' => false,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        // 3. Create Terms for MI (Cawu)
        DB::table('academic_terms')->insert([
            [
                'academic_year_id' => $yearId,
                'academic_scheme_id' => $cawuSchemeId,
                'name' => 'Cawu 1',
                'sequence' => 1,
                'is_active' => false, // Already passed
                'is_grading_open' => false,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'academic_year_id' => $yearId,
                'academic_scheme_id' => $cawuSchemeId,
                'name' => 'Cawu 2',
                'sequence' => 2,
                'is_active' => true, // Current Active
                'is_grading_open' => true, 
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'academic_year_id' => $yearId,
                'academic_scheme_id' => $cawuSchemeId,
                'name' => 'Cawu 3',
                'sequence' => 3,
                'is_active' => false, // Future
                'is_grading_open' => false,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        $this->command->info('Academic Data seeded! (Year: 2024/2025, Active: Sem 1 & Cawu 2)');
    }
}
