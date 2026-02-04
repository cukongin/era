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
use App\Models\Jenjang;
use App\Models\CatatanKehadiran;

class FillAllEmptyClassesSeeder extends Seeder
{
    public function run()
    {
        $this->command->info("=== MENGISI KEKOSONGAN HATI... EH, KELAS ===");

        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        $guru = User::where('role', 'teacher')->first() ?? User::first();

        // Get ALL Classes in Active Year
        $classes = Kelas::where('id_tahun_ajaran', $activeYear->id)->get();

        if ($classes->isEmpty()) {
            $this->command->warn("Tidak ada kelas di tahun ajaran aktif.");
            return;
        }

        // Get Existing Mapels (Real ones)
        $existingMapels = Mapel::all();
        if ($existingMapels->isEmpty()) {
            $this->command->error("Tidak ada Mapel data master.");
            return;
        }

        foreach ($classes as $kelas) {
            $this->command->info("Memeriksa Kelas: {$kelas->nama_kelas} ({$kelas->jenjang->nama_jenjang})");

            // 1. ENSURE STUDENTS
            if ($kelas->anggota_kelas()->count() == 0) {
                $this->command->info("  > Kosong! Menambahkan 10 siswa robot...");
                for ($i = 1; $i <= 10; $i++) {
                    $uniqueCode = $kelas->id . rand(100, 999) . $i;
                    $siswa = Siswa::create([
                        'nama_lengkap' => "Siswa {$kelas->nama_kelas} No.{$i}",
                        'nis_lokal' => "NIS{$uniqueCode}",
                        'nisn' => "00{$uniqueCode}",
                        'tempat_lahir' => 'Sistem',
                        'tanggal_lahir' => '2015-01-01',
                        'jenis_kelamin' => ($i % 2 == 0) ? 'L' : 'P',
                        'agama' => 'Islam'
                    ]);

                    $kelas->anggota_kelas()->create([
                        'id_siswa' => $siswa->id
                        // 'id_tahun_ajaran' => $activeYear->id // Removed based on previous schema discovery
                    ]);
                }
            }
            $students = $kelas->anggota_kelas()->with('siswa')->get();


            // 2. ENSURE MAPELS (Plotting)
            $assignedCount = PengajarMapel::where('id_kelas', $kelas->id)->count();
            if ($assignedCount == 0) {
                $this->command->info("  > Belum ada Mapel. Plotting 8 mapel acak...");
                $randomMapels = $existingMapels->random(min(8, $existingMapels->count()));
                
                foreach ($randomMapels as $mapel) {
                    PengajarMapel::create([
                        'id_kelas' => $kelas->id,
                        'id_mapel' => $mapel->id,
                        'id_guru' => $guru->id
                    ]);

                    // Ensure KKM
                    KkmMapel::updateOrCreate(
                        [
                            'id_mapel' => $mapel->id,
                            'id_tahun_ajaran' => $activeYear->id,
                            'jenjang_target' => $kelas->jenjang->kode
                        ],
                        ['nilai_kkm' => 70]
                    );
                }
            }
            
            // Get Assigned Mapels
            $mapelIds = PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
            $classMapels = Mapel::whereIn('id', $mapelIds)->get();


            // 3. FILL GRADES For All Relevant Periods
            // Get periods for this Jenjang
            $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
                ->where('lingkup_jenjang', $kelas->jenjang->kode)
                ->get();
            
            // If no periods found specific to jenjang (fallback logic? or maybe jenjang code mismatch)
            // Just use all periods of active year if specific returns empty (safe fallback)
            if ($periods->isEmpty()) {
                $periods = Periode::where('id_tahun_ajaran', $activeYear->id)->get();
            }

            foreach ($periods as $periode) {
                // $this->command->info("  > Mengisi nilai periode: {$periode->nama_periode}");
                foreach ($students as $ak) {
                    $siswa = $ak->siswa;

                    // Grades
                    foreach ($classMapels as $mapel) {
                        // Check if grade exists
                        $exists = NilaiSiswa::where('id_siswa', $siswa->id)
                            ->where('id_mapel', $mapel->id)
                            ->where('id_periode', $periode->id)
                            ->exists();
                            
                        if (!$exists) {
                            $score = rand(75, 95);
                            NilaiSiswa::create([
                                'id_siswa' => $siswa->id,
                                'id_mapel' => $mapel->id,
                                'id_kelas' => $kelas->id,
                                'id_periode' => $periode->id,
                                'nilai_akhir' => $score,
                                'nilai_akhir_asli' => $score,
                                'predikat' => ($score >= 90 ? 'A' : ($score >= 80 ? 'B' : 'C')),
                                'is_katrol' => false
                            ]);
                        }
                    }

                    // Absensi (One per period)
                    CatatanKehadiran::updateOrCreate(
                        [
                            'id_siswa' => $siswa->id,
                            'id_kelas' => $kelas->id,
                            'id_periode' => $periode->id
                        ],
                        [
                            'sakit' => rand(0, 2),
                            'izin' => rand(0, 2),
                            'tanpa_keterangan' => 0,
                            'kelakuan' => 'Baik',
                            'kerajinan' => 'Baik',
                            'kebersihan' => 'Baik'
                        ]
                    );
                }
            }
        }

        $this->command->info("SELESAI! Semua kelas sekarang penuh dengan data.");
    }
}
