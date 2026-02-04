<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use App\Models\Jenjang;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ensure Jenjang exists
        $mi = Jenjang::where('kode', 'MI')->first();
        $mts = Jenjang::where('kode', 'MTS')->first();

        if (!$mi || !$mts) {
            $this->command->error("Jenjang MI/MTs not found. Run GenericSeeder first.");
            return;
        }

        $faker = \Faker\Factory::create('id_ID');

        // Create 30 MI Students
        for ($i = 1; $i <= 30; $i++) {
            Siswa::create([
                'nama_lengkap' => $faker->name,
                'nis_lokal' => 'MI' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nisn' => $faker->numerify('##########'),
                'nik' => $faker->numerify('################'),
                'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $faker->date('Y-m-d', '2015-01-01'),
                'alamat_lengkap' => $faker->address,
                'nama_ayah' => $faker->name('male'),
                'nama_ibu' => $faker->name('female'),
                'no_telp_ortu' => $faker->phoneNumber,
                'id_jenjang' => $mi->id,
                'tahun_masuk' => 2024,
                'status_siswa' => 'aktif'
            ]);
        }

        // Create 30 MTs Students
        for ($i = 1; $i <= 30; $i++) {
            Siswa::create([
                'nama_lengkap' => $faker->name,
                'nis_lokal' => 'MTS' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nisn' => $faker->numerify('##########'),
                'nik' => $faker->numerify('################'),
                'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $faker->date('Y-m-d', '2012-01-01'),
                'alamat_lengkap' => $faker->address,
                'nama_ayah' => $faker->name('male'),
                'nama_ibu' => $faker->name('female'),
                'no_telp_ortu' => $faker->phoneNumber,
                'id_jenjang' => $mts->id,
                'tahun_masuk' => 2024,
                'status_siswa' => 'aktif'
            ]);
        }
    }
}
