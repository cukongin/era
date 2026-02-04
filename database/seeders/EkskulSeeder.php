<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ekstrakurikuler;

class EkskulSeeder extends Seeder
{
    public function run()
    {
        $ekskuls = [
            ['nama_ekskul' => 'Pramuka', 'jenis' => 'wajib'],
            ['nama_ekskul' => 'Futsal', 'jenis' => 'pilihan'],
            ['nama_ekskul' => 'Hadrah', 'jenis' => 'pilihan'],
            ['nama_ekskul' => 'Drumband', 'jenis' => 'pilihan'],
            ['nama_ekskul' => 'PMR', 'jenis' => 'pilihan'],
            ['nama_ekskul' => 'Kaligrafi', 'jenis' => 'pilihan'],
        ];

        foreach ($ekskuls as $ek) {
            Ekstrakurikuler::firstOrCreate(['nama_ekskul' => $ek['nama_ekskul']], $ek);
        }
    }
}
