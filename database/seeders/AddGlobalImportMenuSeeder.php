<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

class AddGlobalImportMenuSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Root Menu "Import Center" (Menu Khusus)
        $root = DynamicMenu::firstOrCreate(
            ['title' => 'Import Center'],
            [
                'icon' => 'cloud_upload', // Suitable icon
                'order' => 5, // Order after "Wali Kelas" (4) and before "Settings" (99)
                'url' => '#',
                'is_active' => 1,
                'location' => 'sidebar'
            ]
        );
        $this->assignRoles($root, ['admin']);

        // 2. Add Item "Import Nilai Global"
        $item = DynamicMenu::updateOrCreate(
            ['title' => 'Import Nilai Global', 'parent_id' => $root->id],
            [
                'icon' => 'dataset',
                'route' => 'grade.import.global.index',
                'url' => null,
                'order' => 1,
                'is_active' => 1,
                'location' => 'sidebar'
            ]
        );
        $this->assignRoles($item, ['admin']);
    }

    private function assignRoles($menu, $roles)
    {
        foreach ($roles as $role) {
            MenuRole::firstOrCreate([
                'menu_id' => $menu->id,
                'role' => $role
            ]);
        }
    }
}
