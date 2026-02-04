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
use App\Models\AnggotaKelas;
use Illuminate\Support\Facades\DB;

class FillHistorySeeder extends Seeder
{
    public function run()
    {
        $this->command->info("=== MEMBANGUN MESIN WAKTU (HISTORY DATA) - REVISI ===");

        // 0. Cleanup (Aggressive)
        // 1. Delete based on suffix
        $ghostClasses = Kelas::where('nama_kelas', 'LIKE', '%(History)%')->get();
        foreach($ghostClasses as $gc) {
            DB::table('anggota_kelas')->where('id_kelas', $gc->id)->delete();
            DB::table('nilai_siswa')->where('id_kelas', $gc->id)->delete();
            DB::table('pengajar_mapel')->where('id_kelas', $gc->id)->delete();
            $gc->delete();
        }
        
        // 2. Delete based on Target Years (2020-2025) using Years created by this seeder
        $yearNames = [
            'T.A. 2020/2021', 'T.A. 2021/2022', 'T.A. 2022/2023', 'T.A. 2023/2024', 'T.A. 2024/2025'
        ];
        $historyYearsIds = TahunAjaran::whereIn('nama', $yearNames)->pluck('id');
        if ($historyYearsIds->isNotEmpty()) {
            // Delete ANY class in these 'fake' history years to be safe
            $ghostClasses = Kelas::whereIn('id_tahun_ajaran', $historyYearsIds)->get();
            foreach($ghostClasses as $gc) {
                DB::table('anggota_kelas')->where('id_kelas', $gc->id)->delete();
                DB::table('nilai_siswa')->where('id_kelas', $gc->id)->delete();
                DB::table('pengajar_mapel')->where('id_kelas', $gc->id)->delete();
                $gc->delete();
            }
        }
        $this->command->info("Data history lama dibersihkan total.");

        // 1. Setup Past Years (Back to 2020/2021)
        // Note: Active year is 2025/2026.
        // History: 2024/2025 (Class 5), 2023/2024 (Class 4)...
        $years = [
            '2020/2021' => ['T.A. 2020/2021', false],
            '2021/2022' => ['T.A. 2021/2022', false],
            '2022/2023' => ['T.A. 2022/2023', false],
            '2023/2024' => ['T.A. 2023/2024', false],
            '2024/2025' => ['T.A. 2024/2025', false],
        ];

        $historyYears = [];
        foreach ($years as $kode => $data) {
            $y = TahunAjaran::firstOrCreate(
                ['nama' => $data[0]],
                ['status' => $data[1] ? 'aktif' : 'non-aktif'] 
            );
            $historyYears[$kode] = $y;
        }

        $activeYear = TahunAjaran::where('status', 'aktif')->first();

        // 2. Identify Current Class 6 Students
        $kelas6List = Kelas::where('nama_kelas', 'LIKE', '%6%')
            ->where('id_tahun_ajaran', $activeYear->id)
            ->get();

        if ($kelas6List->isEmpty()) {
            $this->command->error("Tidak ada Kelas 6 di tahun aktif.");
            return;
        }

        foreach ($kelas6List as $kelas6) {
            $students = $kelas6->anggota_kelas()->with('siswa')->get();
            
            // Mapels Class 6 (Base List)
            $baseMapelIds = PengajarMapel::where('id_kelas', $kelas6->id)->pluck('id_mapel')->toArray();

            $historyMap = [
                '2024/2025' => ['level' => 5],
                '2023/2024' => ['level' => 4],
                '2022/2023' => ['level' => 3],
                '2021/2022' => ['level' => 2],
                '2020/2021' => ['level' => 1],
            ];

            foreach ($historyMap as $yearCode => $meta) {
                // Ensure year exists (if using logic shift, hardcoded here is fine for now)
                if (!isset($historyYears[$yearCode])) continue;
                
                $targetYear = $historyYears[$yearCode];
                $targetLevel = $meta['level'];
                
                // Safe Name: "Kelas X (History)"
                $ghostClassName = "Kelas {$targetLevel} (History)"; 
                $jenjangCode = $kelas6->jenjang->kode;
                $jenjang = \App\Models\Jenjang::where('kode', $jenjangCode)->first();

                // Check duplicate class name in that year
                $historyClass = Kelas::firstOrCreate(
                    [
                        'id_tahun_ajaran' => $targetYear->id,
                        'nama_kelas' => $ghostClassName, 
                        'id_jenjang' => $jenjang->id 
                    ],
                    [
                        'tingkat_kelas' => $targetLevel,
                        'id_wali_kelas' => User::where('role', 'teacher')->first()->id
                    ]
                );

                // Periods
                $periods = [];
                for ($p=1; $p<=3; $p++) {
                    $periods[] = Periode::firstOrCreate(
                        [
                            'id_tahun_ajaran' => $targetYear->id,
                            'nama_periode' => "Cawu $p",
                            'lingkup_jenjang' => $jenjangCode
                        ],
                        [
                            'status' => 'tutup',
                            'tenggat_waktu' => now()->subYears(2)
                        ]
                    );
                }

                // Smart Mapel Plotting (Curriculum Based on Name)
                $curriculumMap = [
                    // Level 1 & 2 (Ula Awal)
                    '1-2' => ['Imla\'', 'Tauhid', 'Fiqih', 'Akhlak', 'Aswaja', 'Tajwid', 'Bahasa Arab', 'Tahfidz'],
                    // Level 3 & 4 (Ula Wustho) - Start Grammar
                    '3-4' => ['Nahwu', 'Shorof', 'Tareh', 'Hadits', 'Tafsir', 'I\'lal'],
                    // Level 5 & 6 (Ula Ulya) - Advanced
                    '5-6' => ['Balaghoh', '\'Arudh', 'Usul', 'Faroidl', 'Qowa\'id']
                ];

                $baseMapels = \App\Models\Mapel::whereIn('id', $baseMapelIds)->get();
                $selectedMapels = [];

                foreach ($baseMapels as $m) {
                    $name = $m->nama_mapel;
                    $include = false;

                    // Always include Basics if not explicitly advanced
                    // But wait, user said "Nahwu hanya kelas 3-6". So exclude Nahwu from 1-2.
                    
                    if ($targetLevel <= 2) {
                        // Class 1-2: Only Basic whitelist
                        foreach ($curriculumMap['1-2'] as $k) {
                            if (stripos($name, $k) !== false) $include = true;
                        }
                        // Explicitly exclude advanced keywords if they accidentally matched
                        foreach (array_merge($curriculumMap['3-4'], $curriculumMap['5-6']) as $k) {
                            if (stripos($name, $k) !== false) $include = false;
                        }
                    } elseif ($targetLevel <= 4) {
                        // Class 3-4: Basics + Intermediate
                        // Include Basics
                         foreach ($curriculumMap['1-2'] as $k) {
                            if (stripos($name, $k) !== false) $include = true;
                        }
                        // Include Intermediate
                        foreach ($curriculumMap['3-4'] as $k) {
                            if (stripos($name, $k) !== false) $include = true;
                        }
                         // Exclude Advanced
                        foreach ($curriculumMap['5-6'] as $k) {
                            if (stripos($name, $k) !== false) $include = false;
                        }
                    } else {
                        // Class 5-6: Everything
                        $include = true;
                    }

                    if ($include) {
                        $selectedMapels[] = $m->id;
                    }
                }

                // If selection failed (empty), fallback to random to avoid errors
                if (empty($selectedMapels)) {
                    $selectedMapels = array_slice($baseMapelIds, 0, 5); 
                }

                // Plot Mapels
                foreach ($selectedMapels as $mid) {
                    PengajarMapel::firstOrCreate([
                        'id_kelas' => $historyClass->id,
                        'id_mapel' => $mid
                    ], ['id_guru' => $historyClass->id_wali_kelas]);
                }

                // Enroll & Grades
                foreach ($students as $ak) {
                    AnggotaKelas::firstOrCreate([
                        'id_siswa' => $ak->id_siswa,
                        'id_kelas' => $historyClass->id
                    ]);

                    foreach ($periods as $periode) {
                        foreach ($selectedMapels as $mid) {
                            $gExists = NilaiSiswa::where('id_siswa', $ak->id_siswa)
                                ->where('id_mapel', $mid)
                                ->where('id_periode', $periode->id)
                                ->exists();
                            
                            if (!$gExists) {
                                $score = rand(70, 95);
                                NilaiSiswa::create([
                                    'id_siswa' => $ak->id_siswa,
                                    'id_mapel' => $mid,
                                    'id_kelas' => $historyClass->id,
                                    'id_periode' => $periode->id,
                                    'nilai_akhir' => $score,
                                    'nilai_akhir_asli' => $score,
                                    'predikat' => 'B',
                                    'is_katrol' => false
                                ]);
                            }
                        }
                    }
                }
            }
        }
        $this->command->info("SELESAI! Data history sudah direvisi (Mapel bervariasi per jenjang).");
    }
}
