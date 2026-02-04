<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IdentitasSekolah;

class SchoolIdentitySeeder extends Seeder
{
    public function run()
    {
        // MI
        if (!IdentitasSekolah::where('jenjang', 'MI')->exists()) {
            IdentitasSekolah::create([
                'jenjang' => 'MI',
                'nama_sekolah' => 'MI Al-Hidayah',
                'nsm' => '111235070001',
                'npsn' => '60701234',
                'alamat' => 'Jl. KH. Hasyim Asyari No. 10',
                'desa' => 'Karangploso',
                'kecamatan' => 'Karangploso',
                'kabupaten' => 'Malang',
                'provinsi' => 'Jawa Timur',
                'no_telp' => '0341-123456',
                'email' => 'mi.alhidayah@example.com',
                'kepala_madrasah' => 'H. Zulkifli, S.Pd.I',
                'nip_kepala' => '198001012005011001'
            ]);
        }

        // MTs
        if (!IdentitasSekolah::where('jenjang', 'MTS')->exists()) {
            IdentitasSekolah::create([
                'jenjang' => 'MTS',
                'nama_sekolah' => 'MTs Al-Hidayah',
                'nsm' => '121235070002',
                'npsn' => '60705678',
                'alamat' => 'Jl. KH. Hasyim Asyari No. 10',
                'desa' => 'Karangploso',
                'kecamatan' => 'Karangploso',
                'kabupaten' => 'Malang',
                'provinsi' => 'Jawa Timur',
                'no_telp' => '0341-123456',
                'email' => 'mts.alhidayah@example.com',
                'kepala_madrasah' => 'Drs. H. Abdullah',
                'nip_kepala' => '197505052000031002'
            ]);
        }
    }
}
