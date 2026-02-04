<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

class AddLegerMenuSeeder extends Seeder
{
    public function run()
    {
        // 1. Find 'Wali Kelas' Menu (Parent)
        $waliMenu = DynamicMenu::where('title', 'Wali Kelas')->first();
        if ($waliMenu) {
            
            // 2. Create 'Leger Nilai' Submenu if not exists
            $legerMenu = DynamicMenu::firstOrCreate(
                [
                    'title' => 'Leger Nilai',
                    'route' => 'reports.leger', 
                    'parent_id' => $waliMenu->id
                ],
                [
                    'icon' => 'table_view', // Material Symbol
                    'order' => 5, // After 'Cetak Rapor' (usually 4)
                    'is_active' => 1
                ]
            );

            // 3. Assign Permissions
            $roles = ['admin', 'walikelas', 'staff_tu'];
            
            foreach ($roles as $role) {
                MenuRole::firstOrCreate([
                    'menu_id' => $legerMenu->id,
                    'role' => $role
                ]);
            }
        }
    }
}
