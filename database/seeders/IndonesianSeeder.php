<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Jenjang;
use App\Models\TahunAjaran;
use Faker\Factory as Faker;

class IndonesianSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        // 1. Admin & Guru
        DB::table('users')->insert([
            'name' => 'Administrator',
            'email' => 'admin@madrasah.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'created_at' => now(), 'updated_at' => now()
        ]);

        $guruNames = ['Ustadz Yusuf', 'Ustadzah Fatima', 'Pak Bambang', 'Bu Rina', 'Pak Ali'];
        foreach ($guruNames as $name) {
            $user = User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '', $name)) . '@madrasah.com',
                'password' => Hash::make('password'),
                'role' => 'teacher'
            ]);

            DB::table('data_guru')->insert([
                'id_user' => $user->id,
                'nip' => $faker->numerify('19##########'),
                'nuptk' => $faker->numerify('20##########'),
                'tempat_lahir' => $faker->city,
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // 2. Referensi
        DB::table('jenjang')->insert([
            ['kode' => 'MI', 'nama' => 'Madrasah Ibtidaiyah'],
            ['kode' => 'MTS', 'nama' => 'Madrasah Tsanawiyah'],
        ]);

        $thnId = DB::table('tahun_ajaran')->insertGetId([
            'nama' => '2024/2025',
            'status' => 'aktif',
            'tanggal_mulai' => '2024-07-15',
            'created_at' => now(), 'updated_at' => now()
        ]);

        DB::table('mapel')->insert([
            ['nama_mapel' => 'Quran Hadits', 'kode_mapel' => 'QH', 'kategori' => 'AGAMA'],
            ['nama_mapel' => 'Matematika', 'kode_mapel' => 'MTK', 'kategori' => 'UMUM'],
            ['nama_mapel' => 'Bahasa Arab', 'kode_mapel' => 'BA', 'kategori' => 'AGAMA'],
        ]);

        // 3. Siswa & Kelas
        $miId = Jenjang::where('kode', 'MI')->value('id');
        $mtsId = Jenjang::where('kode', 'MTS')->value('id');
        $guruIds = User::where('role', 'teacher')->pluck('id')->toArray();

        // Kelas MI (1-6)
        for ($i = 1; $i <= 6; $i++) {
            $kelasId = DB::table('kelas')->insertGetId([
                'id_tahun_ajaran' => $thnId,
                'id_jenjang' => $miId,
                'nama_kelas' => $i . '-A',
                'tingkat_kelas' => $i,
                'id_wali_kelas' => $guruIds[array_rand($guruIds)],
                'created_at' => now(), 'updated_at' => now()
            ]);

            // 10 Siswa per kelas
            for ($j = 0; $j < 10; $j++) {
                $siswaId = DB::table('siswa')->insertGetId([
                    'nama_lengkap' => $faker->name,
                    'nis_lokal' => $faker->unique()->numerify('1122###'),
                    'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                    'id_jenjang' => $miId,
                    'status_siswa' => 'aktif',
                    'created_at' => now(), 'updated_at' => now()
                ]);

                DB::table('anggota_kelas')->insert([
                    'id_siswa' => $siswaId,
                    'id_kelas' => $kelasId,
                    'status' => 'aktif',
                    'created_at' => now(), 'updated_at' => now()
                ]);
            }
        }
    }
}
