<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

class AddAdminImportMenuV2 extends Migration
{
    public function up()
    {
        // 1. Create Parent "Import Data" if not exists
        $parent = DynamicMenu::firstOrCreate(
            ['title' => 'Import Data'],
            [
                'icon' => 'upload_file',
                'order' => 5, 
                'is_active' => true,
                'location' => 'sidebar' 
            ]
        );
        $this->assignRole($parent, 'admin');

        // 2. Child "Absensi & Sikap"
        $child = DynamicMenu::firstOrCreate(
            ['title' => 'Absensi & Sikap', 'parent_id' => $parent->id],
            [
                'icon' => 'fact_check',
                'route' => 'admin.attendance.import.index',
                'order' => 1,
                'is_active' => true,
                'location' => 'sidebar'
            ]
        );
        $this->assignRole($child, 'admin');
    }

    private function assignRole($menu, $role)
    {
        MenuRole::firstOrCreate([
            'menu_id' => $menu->id,
            'role' => $role
        ]);
    }

    public function down()
    {
        // No down
    }
}
