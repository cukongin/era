<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradingFormula;

class GradingFormulaSeeder extends Seeder
{
    public function run()
    {
        $formulas = [
            [
                'name' => 'Rapor MI (Standar)',
                'context' => 'rapor_mi',
                'formula' => '([Rata_PH] * 0.6) + ([Nilai_PAS] * 0.4)',
                'is_active' => true,
                'description' => 'Rumus standar MI: 60% Harian + 40% Ujian Akhir.'
            ],
            [
                'name' => 'Rapor MTs (Standar)',
                'context' => 'rapor_mts',
                'formula' => '([Rata_PH] * 0.5) + ([Nilai_PTS] * 0.2) + ([Nilai_PAS] * 0.3)',
                'is_active' => true,
                'description' => 'Rumus MTs: 50% Harian + 20% PTS + 30% PAS.'
            ],
            [
                'name' => 'Ijazah MI (Standard)',
                'context' => 'ijazah_mi',
                'formula' => '([Rata_Rapor_MI] * 0.6) + ([Nilai_Ujian] * 0.4)',
                'is_active' => true,
                'description' => 'Rumus Kelulusan MI: 60% Rata-rata Rapor (Kls 4,5,6) + 40% Ujian.'
            ],
            [
                'name' => 'Ijazah MTs (Standard)',
                'context' => 'ijazah_mts',
                'formula' => '([Rata_Rapor_MTS] * 0.6) + ([Nilai_Ujian] * 0.4)',
                'is_active' => true,
                'description' => 'Rumus Kelulusan MTs: 60% Rata-rata Rapor (Kls 7,8,9) + 40% Ujian.'
            ],
            [
                'name' => 'Akhir Tahun MI (Cawu)',
                'context' => 'rapor_mi',
                'formula' => '([Nilai_Cawu_1] + [Nilai_Cawu_2] + [Nilai_Cawu_3]) / 3',
                'is_active' => false,
                'description' => 'Rumus Kenaikan Kelas (Rata-rata 3 Cawu).'
            ],
            [
                'name' => 'Full Ujian (100%)',
                'context' => 'rapor_mts',
                'formula' => '[Nilai_PAS]',
                'is_active' => false,
                'description' => 'Nilai rapor diambil 100% dari Ujian Akhir (PAS) saja.'
            ],
            [
                'name' => 'Project Based (Kurmer)',
                'context' => 'rapor_mts',
                'formula' => '([Rata_PH] * 0.7) + ([Nilai_PAS] * 0.3)',
                'is_active' => false,
                'description' => 'Menitikberatkan pada proses (Harian/Proyek) sebesar 70%.'
            ],
            [
                'name' => 'Ijazah 50:50',
                'context' => 'ijazah_mi',
                'formula' => '([Rata_Rapor_MI] * 0.5) + ([Nilai_Ujian] * 0.5)',
                'is_active' => false,
                'description' => 'Bobot seimbang antara Rapor dan Ujian.'
            ],
            [
                'name' => 'Akhir Tahun MTs (Semester)',
                'context' => 'rapor_mts',
                'formula' => '([Nilai_Sem_1] + [Nilai_Sem_2]) / 2',
                'is_active' => false,
                'description' => 'Rumus Kenaikan Kelas (Rata-rata 2 Semester).'
            ],


            [
                'name' => 'Ranking Juara Umum',
                'context' => 'ranking_score',
                'formula' => '[Rata_Rata_Nilai] + ([Absensi_Hadir] * 0.1) + ([Nilai_Ekstrakurikuler] * 0.2)',
                'is_active' => false,
                'description' => 'Ranking ditentukan oleh Rata-rata + Kehadiran + Ekskul.'
            ],
            [
                'name' => 'Ranking Akademik Murni',
                'context' => 'ranking_score',
                'formula' => '[Rata_Rata_Nilai]',
                'is_active' => true,
                'description' => 'Ranking hanya berdasarkan rata-rata nilai akademik.'
            ],
            [
                'name' => 'Total Nilai (Standard)',
                'context' => 'total_score',
                'formula' => 'sum([Nilai_Mapel])',
                'is_active' => true,
                'description' => 'Total adalah penjumlahan semua nilai mapel.'
            ]
        ];

        foreach ($formulas as $f) {
            GradingFormula::updateOrCreate(
                ['name' => $f['name'], 'context' => $f['context']],
                $f
            );
        }
    }
}
