<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Mapel;
use App\Models\NilaiSiswa;
use App\Models\CatatanKehadiran;
use App\Models\NilaiEkskul;
use App\Models\Ekstrakurikuler;
use App\Models\PengajarMapel;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\CatatanWaliKelas;

class FillGrade1MISeeder extends Seeder
{
    public function run()
    {
        // 1. Get Active Context
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) {
            $this->command->error('Tahun Ajaran Aktif tidak ditemukan!');
            return;
        }

        // 2. Find "Kelas 1 MI" (Any active class with tingkat 1 and jenjang MI)
        $targetClass = Kelas::where('id_tahun_ajaran', $activeYear->id)
            ->where('tingkat_kelas', 1)
            ->whereHas('jenjang', function($q) {
                $q->where('kode', 'MI');
            })
            ->first();

        if (!$targetClass) {
            $this->command->error('Kelas 1 MI tidak ditemukan!');
            return;
        }

        $this->command->info("Menyiapkan data untuk Kelas: {$targetClass->nama_kelas}");

        // 3. Get ALL Periods for this Year & Jenjang (Cawu 1, 2, 3)
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('lingkup_jenjang', $targetClass->jenjang->kode)
            ->get();
            
        if ($periods->isEmpty()) {
            $this->command->error("Tidak ada periode ditemukan untuk jenjang {$targetClass->jenjang->kode}!");
            return;
        }

        // 4. Get Students
        $students = $targetClass->anggota_kelas()->with('siswa')->get();
        if ($students->isEmpty()) {
            $this->command->error('Kelas ini kosong melompong!');
            return;
        }

        // 5. Get Mapels assigned to this class
        $mapelIds = PengajarMapel::where('id_kelas', $targetClass->id)->pluck('id_mapel');
        if ($mapelIds->isEmpty()) {
            $this->command->warn('Kelas ini belum punya Mapel. Gunakan tombol "Generate Paket Mapel" dulu!');
            return;
        }
        $mapels = Mapel::whereIn('id', $mapelIds)->get();

        // Loop per Period
        foreach ($periods as $periode) {
            $this->command->info("=== Mengisi Data untuk Periode: {$periode->nama_periode} ({$periode->status}) ===");

            // 6. Loop Students and Fill Data
            foreach ($students as $ak) {
                $siswa = $ak->siswa;
                // Silent inner loop info to reduce spam, or just show summary
                // $this->command->info(" Mengisi data siswa: {$siswa->nama_lengkap}");

                // A. NILAI AKADEMIK (Mata Pelajaran)
                foreach ($mapels as $mapel) {
                    $score = rand(75, 95); 
                    if (rand(1, 100) <= 10) $score = rand(50, 69);
    
                    NilaiSiswa::updateOrCreate(
                        [
                            'id_siswa' => $siswa->id,
                            'id_mapel' => $mapel->id,
                            'id_kelas' => $targetClass->id,
                            'id_periode' => $periode->id
                        ],
                        [
                            'nilai_akhir' => $score, 
                            'nilai_akhir_asli' => $score,
                            'is_katrol' => false,
                            'predikat' => $this->getPredikat($score)
                        ]
                    );
                }
    
                // B. ABSENSI / KEHADIRAN
                CatatanKehadiran::updateOrCreate(
                    [
                        'id_siswa' => $siswa->id,
                        'id_kelas' => $targetClass->id,
                        'id_periode' => $periode->id
                    ],
                    [
                        'sakit' => rand(0, 2),
                        'izin' => rand(0, 2),
                        'tanpa_keterangan' => rand(0, 1),
                        'kelakuan' => 'Baik',
                        'kerajinan' => 'Baik',
                        'kebersihan' => 'Baik'
                    ]
                );
    
                // C. CATATAN WALI KELAS
                $notes = [
                    "Tingkatkan terus prestasimu, Nak!",
                    "Rajin belajar dan jangan lupa berdoa.",
                    "Perbaiki kehadiran di semester depan ya.",
                    "Anak yang sholeh/sholehah, pertahankan!",
                    "Semangat belajar, masa depan cerah menanti."
                ];
                CatatanWaliKelas::updateOrCreate(
                    [
                        'id_siswa' => $siswa->id,
                        'id_kelas' => $targetClass->id,
                        'id_periode' => $periode->id
                    ],
                    [
                        'catatan_akademik' => $notes[array_rand($notes)], 
                    ]
                );
    
                // D. EKSKUL (Random 1-2 ekskul)
                $ekskulNames = ['Pramuka', 'PMR', 'Futsal', 'Hadrah', 'Qiroah'];
                $selectedEkskuls = collect($ekskulNames)->random(rand(1, 2));
                
                // Clear old first
                NilaiEkskul::where('id_siswa', $siswa->id)->where('id_periode', $periode->id)->delete();
                
                foreach($selectedEkskuls as $ekName) {
                    NilaiEkskul::create([
                        'id_siswa' => $siswa->id,
                        'id_kelas' => $targetClass->id,
                        'id_periode' => $periode->id,
                        'nama_ekskul' => $ekName, 
                        'nilai' => ['A', 'B'][array_rand(['A', 'B'])],
                        'keterangan' => 'Aktif mengikuti kegiatan.' 
                    ]);
                }
            }
        }
        
        $this->command->info("Selesai! Data Raport Kelas 1 MI sudah penuh.");
    }

    private function getPredikat($score) {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        return 'D';
    }
}
