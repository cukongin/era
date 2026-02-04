<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\User;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        // 1. Seed Teacher Profiles
        $teachers = User::where('role', 'teacher')->get();
        foreach ($teachers as $teacher) {
            DB::table('teacher_profiles')->insertOrIgnore([
                'user_id' => $teacher->id,
                'nuptk' => $faker->numerify('################'),
                'specialization' => $faker->randomElement(['Quran Hadits', 'Matematika', 'IPA', 'Bahasa Arab']),
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // 2. Seed Students
        $miLevelId = DB::table('levels')->where('code', 'MI')->value('id');
        $mtsLevelId = DB::table('levels')->where('code', 'MTS')->value('id');

        // MI Students (e.g., 50 students)
        for ($i = 0; $i < 50; $i++) {
            DB::table('students')->insert([
                'name' => $faker->name,
                'nis' => $faker->unique()->numerify('1122#####'),
                'gender' => $faker->randomElement(['L', 'P']),
                'level_id' => $miLevelId,
                'status' => 'active',
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // MTs Students (e.g., 30 students)
        for ($i = 0; $i < 30; $i++) {
            DB::table('students')->insert([
                'name' => $faker->name,
                'nis' => $faker->unique()->numerify('2233#####'),
                'gender' => $faker->randomElement(['L', 'P']),
                'level_id' => $mtsLevelId,
                'status' => 'active',
                'created_at' => now(), 'updated_at' => now()
            ]);
        }
    }
}
