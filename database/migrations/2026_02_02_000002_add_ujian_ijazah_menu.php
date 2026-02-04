<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

return new class extends Migration
{
    public function up()
    {
        // Find 'Pengaturan' or 'Settings' parent
        $parent = DynamicMenu::where('title', 'like', '%Pengaturan%')
                    ->orWhere('title', 'like', '%Settings%')
                    ->first();

        // If not found, create one (Example fallback, though usually it exists)
        if (!$parent) {
            $parent = DynamicMenu::create([
                'title' => 'Pengaturan',
                'icon' => 'settings',
                'order' => 99,
                'location' => 'sidebar'
            ]);
            MenuRole::create(['menu_id' => $parent->id, 'role' => 'admin']);
        }

        // Create Child Menu
        $menu = DynamicMenu::firstOrCreate(
            ['title' => 'Ujian Ijazah', 'parent_id' => $parent->id],
            [
                'icon' => 'school',
                'route' => 'settings.ijazah.index',
                'order' => 10, // Adjust order as needed
                'is_active' => true,
                'location' => 'sidebar'
            ]
        );

        // Assign to Admin
        MenuRole::firstOrCreate([
            'menu_id' => $menu->id,
            'role' => 'admin'
        ]);
    }

    public function down()
    {
        // Optional
    }
};
