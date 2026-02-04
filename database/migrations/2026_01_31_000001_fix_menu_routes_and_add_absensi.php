<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

class FixMenuRoutesAndAddAbsensi extends Migration
{
    public function up()
    {
        // 1. Fix 'Aturan Penilaian' -> point to 'settings.index'
        $gradingMenu = DynamicMenu::where('title', 'Aturan Penilaian')->first();
        if ($gradingMenu) {
            $gradingMenu->update([
                'route' => 'settings.index',
                'url' => null 
            ]);
        }

        // 2. Add 'Input Absensi' to 'Wali Kelas' parent
        $waliParent = DynamicMenu::where('title', 'Wali Kelas')->first();
        if ($waliParent) {
            
            // Check if Input Absensi exists, if not create
            $absensi = DynamicMenu::firstOrCreate(
                ['title' => 'Input Absensi', 'parent_id' => $waliParent->id],
                [
                    'icon' => 'fact_check',
                    'route' => 'walikelas.absensi',
                    'order' => 2 // Put it early
                ]
            );
            
            // Should ensure route is correct if it already existed
            if ($absensi->route !== 'walikelas.absensi') {
                $absensi->update(['route' => 'walikelas.absensi']);
            }
            
            $this->assignRoles($absensi, ['admin', 'walikelas']);

            // 3. Add 'Kenaikan Kelas'
            $kenaikan = DynamicMenu::firstOrCreate(
                ['title' => 'Kenaikan Kelas', 'parent_id' => $waliParent->id],
                [
                    'icon' => 'upgrade',
                    'route' => 'walikelas.kenaikan.index',
                    'order' => 5
                ]
            );
            $this->assignRoles($kenaikan, ['admin', 'walikelas']);
            
            // 4. Update 'Konfigurasi Sistem' if exists to be same as 'settings.index' or merge
            // If we have both "Aturan Penilaian" and "Konfigurasi Sistem" pointing to same route, it's redundant.
            // But let's leave it to user preference or update icon.
        }
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

    public function down()
    {
        // No specific down action required as this fixes data
    }
}
