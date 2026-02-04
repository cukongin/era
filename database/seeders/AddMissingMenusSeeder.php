<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

class AddMissingMenusSeeder extends Seeder
{
    public function run()
    {
        // Get 'Pengaturan' Parent
        $settings = DynamicMenu::where('title', 'Pengaturan')->first();

        if ($settings) {
            // 1. Konfigurasi Sistem (settings.index)
            // Existing 'settings.index' might be used for 'Aturan Penilaian'? 
            // In original sidebar:
            // - Konfigurasi Sistem -> settings.index
            // - Aturan Penilaian -> settings.grading (was settings.grading-rules.index)
            
            // Check if exists logic omitted for brevity, assuming run once.
            
            $this->createSubMenu($settings, 'Konfigurasi Sistem', 'calendar_month', 'settings.index', 1, ['admin']); // Order 1, push others?

            // 2. Tenggat & Kunci
            $this->createSubMenu($settings, 'Tenggat & Kunci', 'timer_off', 'settings.deadline.index', 5, ['admin']);

            // 3. Template Rapor
            $this->createSubMenu($settings, 'Template Rapor', 'description', 'settings.templates.index', 6, ['admin']);
            
            // Re-order menus if needed, but append is fine for now.
        }
    }

    private function createSubMenu($parent, $title, $icon, $route, $order, $roles)
    {
        // Avoid duplicate by checking route
        if (DynamicMenu::where('route', $route)->exists()) return;

        $menu = DynamicMenu::create([
            'title' => $title,
            'icon' => $icon,
            'route' => $route == '#' ? null : $route,
            'url' => $route == '#' ? '#' : null,
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
