<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

class TuMenuSeeder extends Seeder
{
    public function run()
    {
        // 1. Dashboard TU
        $dashboard = DynamicMenu::firstOrCreate(
            ['route' => 'tu.dashboard'],
            ['title' => 'Dashboard TU', 'icon' => 'dashboard', 'order' => 1, 'is_active' => true]
        );
        MenuRole::firstOrCreate(['menu_id' => $dashboard->id, 'role' => 'staff_tu']);

        // 2. DKN / Nilai Ijazah
        $dkn = DynamicMenu::firstOrCreate(
            ['route' => 'tu.dkn.index'],
            ['title' => 'Nilai Ijazah (DKN)', 'icon' => 'school', 'order' => 2, 'is_active' => true]
        );
        MenuRole::firstOrCreate(['menu_id' => $dkn->id, 'role' => 'staff_tu']);

        $this->command->info('Menu TU seeded.');
    }
}
