<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\BobotPenilaian;

class GradingSeeder extends Seeder
{
    public function run()
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->first();

        if ($activeYear) {
            // 1. Seed Periods
            $periods = [
                ['nama_periode' => 'Cawu 1', 'tipe' => 'CAWU', 'lingkup_jenjang' => 'MI'],
                ['nama_periode' => 'Cawu 2', 'tipe' => 'CAWU', 'lingkup_jenjang' => 'MI'],
                ['nama_periode' => 'Cawu 3', 'tipe' => 'CAWU', 'lingkup_jenjang' => 'MI'],
                ['nama_periode' => 'Semester Ganjil', 'tipe' => 'SEMESTER', 'lingkup_jenjang' => 'MTS'],
                ['nama_periode' => 'Semester Genap', 'tipe' => 'SEMESTER', 'lingkup_jenjang' => 'MTS'],
            ];

            foreach ($periods as $p) {
                Periode::firstOrCreate(
                    ['id_tahun_ajaran' => $activeYear->id, 'nama_periode' => $p['nama_periode']],
                    $p
                );
            }

            // 2. Seed Default Weights (already handled in Controller, but good to have here)
            BobotPenilaian::firstOrCreate(
                ['id_tahun_ajaran' => $activeYear->id, 'jenjang' => 'MI'],
                ['bobot_harian' => 50, 'bobot_uts_cawu' => 50, 'bobot_uas' => 0]
            );

            BobotPenilaian::firstOrCreate(
                ['id_tahun_ajaran' => $activeYear->id, 'jenjang' => 'MTS'],
                ['bobot_harian' => 40, 'bobot_uts_cawu' => 30, 'bobot_uas' => 30]
            );
        }
    }
}
