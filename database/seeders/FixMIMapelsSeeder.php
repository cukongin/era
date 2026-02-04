<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Mapel;
use App\Models\NilaiSiswa;
use App\Models\PengajarMapel;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\User;
use App\Models\KkmMapel;
use Illuminate\Support\Facades\DB;

class FixMIMapelsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info("=== MEMPERBAIKI DATA MAPEL & NILAI MI ===");

        // 1. CLEANUP (Hapus Mapel yang baru saya tambahkan: ID 26-100)
        // Adjust ID range based on observation (26-35 were added)
        $idsToDelete = range(26, 100); 
        
        $count = Mapel::whereIn('id', $idsToDelete)->count();
        if ($count > 0) {
            $this->command->warn("Menghapus $count mapel sampah (ID 26-100)...");
            
            // Delete dependencies first (to be safe, though cascade might handle it)
            PengajarMapel::whereIn('id_mapel', $idsToDelete)->delete();
            NilaiSiswa::whereIn('id_mapel', $idsToDelete)->delete();
            KkmMapel::whereIn('id_mapel', $idsToDelete)->delete();
            Mapel::whereIn('id', $idsToDelete)->delete();
        } else {
            $this->command->info("Tidak ada mapel sampah ditemukan.");
        }

        // 2. PREPARE RE-SEEDING
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        $guru = User::where('role', 'teacher')->firstOrFail();
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
                ->where('lingkup_jenjang', 'MI')
                ->get();
        
        // 3. GET EXISTING MAPELS (IDs < 26)
        $existingMapels = Mapel::where('id', '<', 26)->get();
        if ($existingMapels->isEmpty()) {
            $this->command->error("Gawat! Tidak ada Mapel asli ditemukan (ID < 26).");
            return;
        }

        // 4. LOOP CLASSES AND RE-ASSIGN
        $classes = Kelas::where('id_tahun_ajaran', $activeYear->id)
            ->whereHas('jenjang', fn($q) => $q->where('kode', 'MI'))
            ->get();

        foreach ($classes as $kelas) {
            $this->command->info("Mengatur ulang kelas: {$kelas->nama_kelas}");

            // Plotting: Assign 8 Random Existing Mapels
            // Shuffle and take 8
            $classMapels = $existingMapels->random(min(8, $existingMapels->count()));

            foreach ($classMapels as $mapel) {
                // Assign Guru
                PengajarMapel::updateOrCreate(
                    [
                        'id_kelas' => $kelas->id,
                        'id_mapel' => $mapel->id
                    ],
                    [
                        'id_guru' => $guru->id
                    ]
                );

                // Ensure KKM exists
                KkmMapel::updateOrCreate(
                    [
                        'id_mapel' => $mapel->id,
                        'id_tahun_ajaran' => $activeYear->id,
                        'jenjang_target' => 'MI'
                    ],
                    ['nilai_kkm' => 70]
                );
            }

            // Get Students
            $students = $kelas->anggota_kelas()->with('siswa')->get();

            // Seed Grades for these Mapels & All Periods
            foreach ($periods as $periode) {
                foreach ($students as $ak) {
                    $siswa = $ak->siswa;

                    foreach ($classMapels as $mapel) {
                        $score = rand(70, 98); // Higher scores
                        
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
                                'predikat' => ($score >= 90 ? 'A' : ($score >= 80 ? 'B' : 'C')),
                                'is_katrol' => false
                            ]
                        );
                    }
                }
            }
        }
        
        $this->command->info("SELESAI! Data Mapel sudah bersih dan Kelas MI sudah punya nilai baru.");
    }
}
