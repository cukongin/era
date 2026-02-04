<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\DataGuru;
use App\Models\Mapel;

class SuperDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        // ==========================================
        // 1. DATA MAPEL SUPER (Madrasah Standard)
        // ==========================================
        // DB::table('mapel')->truncate(); // Optional: Uncomment to clear existing
        
        $mapels = [
            // KELOMPOK A (PAI)
            ['nama_mapel' => 'Al-Quran Hadits', 'kode_mapel' => 'QH', 'kategori' => 'AGAMA'],
            ['nama_mapel' => 'Akidah Akhlak', 'kode_mapel' => 'AA', 'kategori' => 'AGAMA'],
            ['nama_mapel' => 'Fiqih', 'kode_mapel' => 'FIQ', 'kategori' => 'AGAMA'],
            ['nama_mapel' => 'Sejarah Kebudayaan Islam', 'kode_mapel' => 'SKI', 'kategori' => 'AGAMA'],
            
            // KELOMPOK B (UMUM)
            ['nama_mapel' => 'Pendidikan Pancasila', 'kode_mapel' => 'PKN', 'kategori' => 'UMUM'],
            ['nama_mapel' => 'Bahasa Indonesia', 'kode_mapel' => 'BIN', 'kategori' => 'UMUM'],
            ['nama_mapel' => 'Bahasa Arab', 'kode_mapel' => 'BAR', 'kategori' => 'AGAMA'], // Often treated as Agama in Madrasah
            ['nama_mapel' => 'Matematika', 'kode_mapel' => 'MTK', 'kategori' => 'UMUM'],
            ['nama_mapel' => 'Ilmu Pengetahuan Alam', 'kode_mapel' => 'IPA', 'kategori' => 'UMUM'],
            ['nama_mapel' => 'Ilmu Pengetahuan Sosial', 'kode_mapel' => 'IPS', 'kategori' => 'UMUM'],
            ['nama_mapel' => 'Bahasa Inggris', 'kode_mapel' => 'ING', 'kategori' => 'UMUM'],
            
            // KELOMPOK C (SENI & PRAKARYA)
            ['nama_mapel' => 'Seni Budaya', 'kode_mapel' => 'SBK', 'kategori' => 'UMUM'],
            ['nama_mapel' => 'Pendidikan Jasmani & Kesehatan', 'kode_mapel' => 'PJOK', 'kategori' => 'UMUM'],
            ['nama_mapel' => 'Prakarya', 'kode_mapel' => 'PRA', 'kategori' => 'UMUM'],
            
            // KELOMPOK D (MULOK)
            ['nama_mapel' => 'Bahasa Daerah (Jawa)', 'kode_mapel' => 'BDJ', 'kategori' => 'MULOK'],
            ['nama_mapel' => 'Ke-NU-an / Aswaja', 'kode_mapel' => 'ASW', 'kategori' => 'MULOK'],
            ['nama_mapel' => 'Tahfidz Quran', 'kode_mapel' => 'THQ', 'kategori' => 'MULOK'],
            ['nama_mapel' => 'Baca Tulis Al-Quran', 'kode_mapel' => 'BTQ', 'kategori' => 'MULOK'],
            ['nama_mapel' => 'Komputer / TIK', 'kode_mapel' => 'TIK', 'kategori' => 'MULOK'],
        ];

        foreach ($mapels as $m) {
            Mapel::updateOrCreate(
                ['kode_mapel' => $m['kode_mapel']],
                $m
            );
        }

        $this->command->info('✅ Mata Pelajaran Super berhasil dibuat!');

        // ==========================================
        // 2. DATA GURU SUPER (20 Orang)
        // ==========================================
        
        $jumlahGuru = 20;
        
        for ($i = 0; $i < $jumlahGuru; $i++) {
            $gender = $faker->randomElement(['L', 'P']);
            $firstName = $gender == 'L' ? $faker->firstNameMale : $faker->firstNameFemale;
            $lastName = $faker->lastName;
            $name = "$firstName $lastName";
            $email = strtolower($firstName) . '.' . $faker->numerify('###') . '@madrasah.id';
            
            // Create User
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'email_verified_at' => now(),
            ]);

            // Create Data Guru Profile
            DataGuru::create([
                'id_user' => $user->id,
                'nip' => $faker->numerify('19##########'),
                'nuptk' => $faker->numerify('20##########'),
                'jenis_kelamin' => $gender,
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $faker->date('Y-m-d', '1995-01-01'),
                'no_hp' => $faker->phoneNumber,
                'alamat' => $faker->address
            ]);
        }

        $this->command->info("✅ $jumlahGuru Guru Super berhasil ditambahkan!");
    }
}
