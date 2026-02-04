<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

class AddTeacherMenusSeeder extends Seeder
{
    public function run()
    {
        // 1. Profil Saya (Under Menu Guru)
        $teacherGroup = DynamicMenu::where('title', 'Menu Guru')->first();

        if ($teacherGroup) {
            // Check if Profil Saya exists
            if (!DynamicMenu::where('title', 'Profil Saya')->where('parent_id', $teacherGroup->id)->exists()) {
                 $profil = DynamicMenu::create([
                    'title' => 'Profil Saya',
                    'icon' => 'person',
                    'route' => 'profile.edit', // Assuming profile route exists or using # for now
                    'url' => '#',
                    'parent_id' => $teacherGroup->id,
                    'order' => 3
                ]);
                
                // Assign to Teacher & Admin
                foreach (['admin', 'teacher'] as $role) {
                    MenuRole::create([
                        'menu_id' => $profil->id,
                        'role' => $role
                    ]);
                }
            }
        }
        
        // 2. Ensure Dashboard has teacher role (already done in DynamicMenuSeeder)
    }
}
