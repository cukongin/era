<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

class AddTuAccessToReportsSeeder extends Seeder
{
    public function run()
    {
        // 1. Find 'Wali Kelas' Menu (Parent)
        $waliMenu = DynamicMenu::where('title', 'Wali Kelas')->first();
        if ($waliMenu) {
            // Assign 'staff_tu' to Parent
            MenuRole::firstOrCreate([
                'menu_id' => $waliMenu->id,
                'role' => 'staff_tu'
            ]);

            // 2. Find 'Cetak Rapor' Submenu
            $cetakMenu = DynamicMenu::where('title', 'Cetak Rapor')
                ->where('parent_id', $waliMenu->id)
                ->first();

            if ($cetakMenu) {
                // Assign 'staff_tu' to Submenu
                MenuRole::firstOrCreate([
                    'menu_id' => $cetakMenu->id,
                    'role' => 'staff_tu'
                ]);
            }
            
            // Optional: 'Leger Nilai' ? Not requested but useful.
            // Let's stick to what's requested: 'Cetak Rapor'
        }
    }
}
