<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Mapel;
use App\Models\NilaiSiswa;
use App\Models\CatatanKehadiran;
use App\Models\NilaiEkskul;
use App\Models\PengajarMapel;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\CatatanWaliKelas;
use App\Models\User;
use App\Models\Jenjang;

class FillMIFullDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Context
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        $jenjangMI = Jenjang::where('kode', 'MI')->firstOrFail();
        
        // Get generic teacher for assignment
        $guru = User::where('role', 'teacher')->first();
        if (!$guru) {
            $this->command->error("Butuh setidaknya satu User Guru!");
            return;
        }

        // 2. Loop Classes 1 to 6
        for ($tingkat = 1; $tingkat <= 6; $tingkat++) {
            $className = "{$tingkat} - MI";
            $this->command->info("--- Memproses Kelas: $className ---");

            // A. Create/Get Class
            $kelas = Kelas::firstOrCreate(
                [
                    'nama_kelas' => $className,
                    'id_tahun_ajaran' => $activeYear->id,
                    'id_jenjang' => $jenjangMI->id
                ],
                [
                    'tingkat_kelas' => $tingkat,
                    'id_wali_kelas' => $guru->id // Default assign to first teacher
                ]
            );

            // B. Create Students (if empty)
            if ($kelas->anggota_kelas()->count() == 0) {
                $this->command->info("Creating students for $className...");
                // Create 10 students
                for ($i = 1; $i <= 10; $i++) {
                    $nis = "10{$tingkat}0{$i}"; // Example NIS
                    $siswa = Siswa::firstOrCreate(
                        ['nis_lokal' => $nis],
                        [
                            'nama_lengkap' => "Siswa {$tingkat}MI No.{$i}",
                            'nisn' => "00$nis",
                            'tempat_lahir' => 'Surabaya',
                            'tanggal_lahir' => '2015-01-01',
                            'jenis_kelamin' => ($i % 2 == 0) ? 'P' : 'L',
                            'agama' => 'Islam'
                        ]
                    );

                    // Add to Kelas
                    $kelas->anggota_kelas()->firstOrCreate(
                        ['id_siswa' => $siswa->id]
                        // ['id_tahun_ajaran' => $activeYear->id] // Removed
                    );
                }
            }
            // Refresh students
            $students = $kelas->anggota_kelas()->with('siswa')->get();


            // C. Assign Mapels (Plotting)
            // Define Standard Mapels per Tingkat if possible, or just use general list
            $standardMapels = ['Al-Quran Hadits', 'Akidah Akhlak', 'Fikih', 'Sejarah Kebudayaan Islam', 'Bahasa Arab', 'Bahasa Indonesia', 'Matematika', 'IPA', 'IPS', 'PKn'];
            
            // Limit mapels for lower grades if needed (e.g. 1-3 Tematik usually, but let's stick to Mapel based for now or simplified)
            if ($tingkat <= 3) {
                // Simplified list for 1-3
                $standardMapels = ['Al-Quran Hadits', 'Akidah Akhlak', 'Fikih', 'Bahasa Arab', 'Bahasa Indonesia', 'Matematika', 'PKn', 'SBdP'];
            }

            foreach ($standardMapels as $mapelName) {
                // Find or Create Master Mapel
                $mapel = Mapel::firstOrCreate(
                    [
                        'nama_mapel' => $mapelName,
                        'target_jenjang' => 'MI' // Ensure MI
                    ],
                    [
                        'kode_mapel' => strtoupper(substr($mapelName, 0, 3)),
                        'kategori' => 'Wajib'
                    ]
                );

                // Add KKM separately
                \App\Models\KkmMapel::updateOrCreate(
                    [
                        'id_mapel' => $mapel->id,
                        'id_tahun_ajaran' => $activeYear->id,
                        'jenjang_target' => 'MI'
                    ],
                    ['nilai_kkm' => 70]
                );

                // Assign to Class (Plotting)
                PengajarMapel::updateOrCreate(
                    [
                        'id_kelas' => $kelas->id,
                        'id_mapel' => $mapel->id
                    ],
                    [
                        'id_guru' => $guru->id
                        // 'id_tahun_ajaran' => $activeYear->id // Removed as column likely doesn't exist
                    ]
                );
            }

            // Refresh Mapels
            $assignedMapelIds = PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
            $mapels = Mapel::whereIn('id', $assignedMapelIds)->get();


            // D. Seed Grades for All Cawu
            $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
                ->where('lingkup_jenjang', 'MI')
                ->get();

            foreach ($periods as $periode) {
                // $this->command->info("   > Seeding {$periode->nama_periode}...");
                
                foreach ($students as $ak) {
                    $siswa = $ak->siswa;

                    // Grades
                    foreach ($mapels as $mapel) {
                         $score = rand(75, 95);
                         if (rand(1, 100) <= 10) $score = rand(60, 69); // 10% remedial

                         NilaiSiswa::updateOrCreate(
                            [
                                'id_siswa' => $siswa->id,
                                'id_mapel' => $mapel->id,
                                'id_kelas' => $kelas->id,
                                'id_periode' => $periode->id
                            ],
                            [
                                'nilai_akhir' => $score,
                                'nilai_akhir_asli' => $score,
                                'predikat' => ($score >= 90 ? 'A' : ($score >= 80 ? 'B' : 'C'))
                            ]
                         );
                    }

                    // Attendance
                    CatatanKehadiran::updateOrCreate(
                        [
                            'id_siswa' => $siswa->id,
                            'id_kelas' => $kelas->id,
                            'id_periode' => $periode->id
                        ],
                        [
                            'sakit' => rand(0, 3),
                            'izin' => rand(0, 3),
                            'tanpa_keterangan' => rand(0, 1),
                            'kelakuan' => 'Baik',
                            'kerajinan' => 'Baik',
                            'kebersihan' => 'Baik'
                        ]
                    );
                    
                     // C. CATATAN WALI KELAS/Ekskul (Optional)
                     // ... (Keeping it simple for speed)
                }
            }
        }
        
        $this->command->info("FULL MI DATA SEEDING COMPLETED.");
    }
}
