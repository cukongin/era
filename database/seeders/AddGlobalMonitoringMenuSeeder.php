<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddGlobalMonitoringMenuSeeder extends Seeder
{
    public function run()
    {
        // 1. Tentukan Parent (Menu Tata Usaha / Dashboard TU)
        // Kita buat sebagai menu level root atau di bawah TU? 
        // User bilang "Menunya dimana", sebaiknya mudah dicari.
        // Kita taruh di Root atau group "Tata Usaha".
        
        // Cek urutan terakhir
        $maxOrder = DB::table('dynamic_menus')->max('order') ?? 0;

        $menuId = DB::table('dynamic_menus')->insertGetId([
            'title' => 'Global Monitoring',
            'url' => 'tu/monitoring',
            'route' => 'tu.monitoring.global',
            'icon' => 'monitoring', // Material symbol
            'parent_id' => null,
            'order' => $maxOrder + 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Beri Akses ke Admin dan Staff TU
        $roles = ['admin', 'staff_tu'];
        
        foreach ($roles as $role) {
            DB::table('menu_roles')->insert([
                'menu_id' => $menuId,
                'role' => $role,
            ]);
        }
        
        $this->command->info('Menu Global Monitoring berhasil ditambahkan!');
    }
}
