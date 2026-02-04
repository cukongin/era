<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\NilaiSiswa;
use App\Models\AnggotaKelas;

class EndOfYearSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        // 1. Setup Context
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        // Ensure periods exist
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)->get();
        if ($periods->isEmpty()) {
            $this->command->error("No periods found! Please regenerate periods first.");
            return;
        }

        $allMapels = Mapel::all();
        $class = Kelas::where('id_tahun_ajaran', $activeYear->id)->first();
        
        if (!$class) {
            $this->command->error("No active class found!");
            return;
        }

        $this->command->info("Seeding data for Class: " . $class->nama_kelas);

        // 2. Create/Update Students (Target 30 Students)
        // We assume existing students might not be enough or complete
        $currentCount = AnggotaKelas::where('id_kelas', $class->id)->count();
        $target = 30;
        
        for ($i = $currentCount; $i < $target; $i++) {
            $gender = $faker->randomElement(['L', 'P']);
            $student = Siswa::create([
                'nama_lengkap' => $faker->name($gender == 'L' ? 'male' : 'female'),
                'nis_lokal' => $faker->unique()->numberBetween(1000, 9999),
                'nisn' => $faker->unique()->numerify('##########'),
                'nik' => $faker->unique()->numerify('16##############'), // 16 digit NIK
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $faker->dateTimeBetween('-15 years', '-10 years'),
                'jenis_kelamin' => $gender,
                'agama' => 'Islam',
                'alamat_lengkap' => $faker->address,
                'nama_ayah' => $faker->name('male'),
                'nama_ibu' => $faker->name('female'),
                'pekerjaan_ayah' => $faker->jobTitle,
                'pekerjaan_ibu' => 'Ibu Rumah Tangga',
            ]);

            AnggotaKelas::create([
                'id_kelas' => $class->id,
                'id_siswa' => $student->id
            ]);
        }

        // 3. Populate Grades & Attendance
        $students = $class->anggota_kelas()->with('siswa')->get();
        
        foreach ($students as $index => $member) {
            $siswa = $member->siswa;
            $isRetainedCandidate = ($index >= 28); // Last 2 students will fail

            // A. Grades
            foreach ($allMapels as $mapel) {
                // If mapel matches jenjang
                if ($mapel->target_jenjang != 'SEMUA' && $mapel->target_jenjang != $class->jenjang->kode) continue;

                foreach ($periods as $periode) {
                    if ($periode->status == 'tutup') continue; // Only active or all? Let's seed active

                    $score = $isRetainedCandidate ? rand(40, 65) : rand(75, 95);
                    $harian = $score;
                    $uts = $score - rand(0, 5);
                    $uas = $score + rand(0, 5);
                    
                    // Variable Weights based on Jenjang
                    $bobotHarian = ($class->jenjang->kode == 'MI') ? 0.5 : 0.3;
                    $bobotUts = ($class->jenjang->kode == 'MI') ? 0.5 : 0.3;
                    $bobotUas = ($class->jenjang->kode == 'MI') ? 0.0 : 0.4;
                    
                    $nilaiAkhir = ($harian * $bobotHarian) + ($uts * $bobotUts) + ($uas * $bobotUas);

                    NilaiSiswa::updateOrCreate(
                        [
                            'id_siswa' => $siswa->id,
                            'id_kelas' => $class->id,
                            'id_mapel' => $mapel->id,
                            'id_periode' => $periode->id,
                        ],
                        [
                            'nilai_harian' => $harian,
                            'nilai_uts_cawu' => $uts,
                            'nilai_uas' => $uas,
                            'nilai_akhir' => $nilaiAkhir, // Calculated Final Score
                            'predikat' => $nilaiAkhir < 70 ? 'D' : ($nilaiAkhir < 80 ? 'C' : ($nilaiAkhir < 90 ? 'B' : 'A')),
                        ]
                    );
                }
            }

            // B. Attendance
            foreach ($periods as $periode) {
                 if ($periode->status == 'tutup') continue;

                 DB::table('catatan_kehadiran')->updateOrInsert(
                    ['id_siswa' => $siswa->id, 'id_kelas' => $class->id, 'id_periode' => $periode->id],
                    [
                        'sakit' => rand(0, 2),
                        'izin' => rand(0, 2),
                        'tanpa_keterangan' => $isRetainedCandidate ? rand(10, 20) : rand(0, 1),
                    ]
                );
            }
            
            // C. Promotion Decision (Pre-calculated for simulation)
            // Note: Actual decision should be calculated by logic, but valid to seed manual overrides
            DB::table('promotion_decisions')->updateOrInsert(
                ['id_siswa' => $siswa->id, 'id_kelas' => $class->id, 'id_tahun_ajaran' => $activeYear->id],
                [
                    'average_score' => $isRetainedCandidate ? 55 : 85,
                    // 'attendance_score' removed (invalid column)
                    'attendance_percent' => $isRetainedCandidate ? 60 : 100, // Correct column
                    'system_recommendation' => $isRetainedCandidate ? 'retained' : 'promoted',
                    'final_decision' => $isRetainedCandidate ? 'retained' : 'promoted',
                    'notes' => $isRetainedCandidate ? 'Nilai Kurang & Banyak Alpa' : 'Naik Kelas',
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info("Seeding Complete! 2 Students set to Retained (Tinggal Kelas).");
    }
}
