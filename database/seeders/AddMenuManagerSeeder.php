<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

class AddMenuManagerSeeder extends Seeder
{
    public function run()
    {
        $settings = DynamicMenu::where('title', 'Pengaturan')->first();

        if ($settings) {
            // Add 'Manajemen Menu'
            $this->createSubMenu($settings, 'Manajemen Menu', 'menu_open', 'settings.menus.index', 7, ['admin']);
        }
    }

    private function createSubMenu($parent, $title, $icon, $route, $order, $roles)
    {
        if (DynamicMenu::where('route', $route)->exists()) return;

        $menu = DynamicMenu::create([
            'title' => $title,
            'icon' => $icon,
            'route' => $route,
            'parent_id' => $parent->id,
            'order' => $order
        ]);

        foreach ($roles as $role) {
            MenuRole::create([
                'menu_id' => $menu->id,
                'role' => $role
            ]);
        }
    }
}
