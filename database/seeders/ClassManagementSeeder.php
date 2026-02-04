<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClassManagementSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Teachers
        $teachers = [
            ['name' => 'Ustadz Yusuf', 'email' => 'yusuf@madrasah.com'],
            ['name' => 'Ustadzah Fatima', 'email' => 'fatima@madrasah.com'],
            ['name' => 'Mr. Bambang', 'email' => 'bambang@madrasah.com'],
            ['name' => 'Mrs. Rina', 'email' => 'rina@madrasah.com'],
            ['name' => 'Ustadz Ali', 'email' => 'ali@madrasah.com'],
        ];

        foreach ($teachers as $t) {
            DB::table('users')->insertOrIgnore([ // Avoid duplicate email errors
                'name' => $t['name'],
                'email' => $t['email'],
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // 2. Create Subjects
        $subjects = [
            ['name' => 'Quran Hadits', 'code' => 'QH', 'category' => 'RELIGIOUS'],
            ['name' => 'Aqidah Akhlak', 'code' => 'AA', 'category' => 'RELIGIOUS'],
            ['name' => 'Matematika', 'code' => 'MTK', 'category' => 'GENERAL'],
            ['name' => 'IPA', 'code' => 'IPA', 'category' => 'GENERAL'],
            ['name' => 'Bahasa Arab', 'code' => 'BA', 'category' => 'RELIGIOUS'],
            ['name' => 'Bahasa Jawa', 'code' => 'BJ', 'category' => 'MULOK'],
        ];

        foreach ($subjects as $s) {
            DB::table('subjects')->insertOrIgnore([
                'name' => $s['name'],
                'code' => $s['code'],
                'category' => $s['category'],
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // 3. Create Classes (Example)
        $activeYear = DB::table('academic_years')->where('status', 'active')->first();
        if (!$activeYear) return;

        // Get Levels
        $miLevel = DB::table('levels')->where('code', 'MI')->first();
        $mtsLevel = DB::table('levels')->where('code', 'MTS')->first(); // Code was MTS in migration
        
        // Get Teacher IDs array
        $teacherIds = DB::table('users')->where('role', 'teacher')->pluck('id')->toArray();
        $teacherCount = count($teacherIds);

        // MI Classes (1-A to 6-A)
        if ($miLevel) {
            for ($i = 1; $i <= 6; $i++) {
                // Determine homeroom teacher (cycle through teachers)
                $assignedTeacherId = $teacherCount > 0 ? $teacherIds[($i - 1) % $teacherCount] : null;

                DB::table('classes')->insertOrIgnore([
                    'academic_year_id' => $activeYear->id,
                    'level_id' => $miLevel->id,
                    'name' => $i . '-A',
                    'grade_level' => $i,
                    'homeroom_teacher_id' => $assignedTeacherId,
                    'created_at' => now(), 'updated_at' => now()
                ]);
            }
        }

        // MTs Classes (7-A to 9-A)
        if ($mtsLevel) {
            for ($i = 7; $i <= 9; $i++) {
                $assignedTeacherId = $teacherCount > 0 ? $teacherIds[($i - 1) % $teacherCount] : null;

                DB::table('classes')->insertOrIgnore([
                    'academic_year_id' => $activeYear->id,
                    'level_id' => $mtsLevel->id,
                    'name' => $i . '-A',
                    'grade_level' => $i,
                    'homeroom_teacher_id' => $assignedTeacherId,
                    'created_at' => now(), 'updated_at' => now()
                ]);
            }
        }
    }
}
