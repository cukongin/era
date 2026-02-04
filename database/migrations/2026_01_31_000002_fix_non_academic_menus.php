<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

class FixNonAcademicMenus extends Migration
{
    public function up()
    {
        // 1. Find the 'Wali Kelas' parent menu
        $waliParent = DynamicMenu::where('title', 'Wali Kelas')->first();
        if ($waliParent) {
            
            // --- EKSTRAKURIKULER ---
            // Check if there is an existing 'Input Non-Akademik' placeholder or 'Input Ekskul'
            $oldNonAcademic = DynamicMenu::where('title', 'Input Non-Akademik')
                                        ->where('parent_id', $waliParent->id)
                                        ->first();

            if ($oldNonAcademic) {
                // Rename and Repurpose existing placeholder
                $oldNonAcademic->update([
                    'title' => 'Input Ekskul',
                    'route' => 'ekskul.index',
                    'icon' => 'sports_soccer', // Better icon
                    'url' => null
                ]);
                $ekskulMenu = $oldNonAcademic;
            } else {
                // Create New if not exist
                $ekskulMenu = DynamicMenu::firstOrCreate(
                    ['title' => 'Input Ekskul', 'parent_id' => $waliParent->id],
                    [
                        'icon' => 'sports_soccer',
                        'route' => 'ekskul.index',
                        'order' => 3 
                    ]
                );
            }
            // Ensure roles match parent
            $this->assignRoles($ekskulMenu, ['admin', 'walikelas']);


            // --- CATATAN SISWA (Sikap/Prestasi) ---
            $catatanMenu = DynamicMenu::firstOrCreate(
                ['title' => 'Catatan Siswa', 'parent_id' => $waliParent->id],
                [
                    'icon' => 'rate_review',
                    'route' => 'walikelas.catatan.index',
                    'order' => 4
                ]
            );
            $this->assignRoles($catatanMenu, ['admin', 'walikelas']);

            
            // --- CLEANUP ---
            // If there are duplicate "Input Non-Akademik" entries that are still placeholders, remove them
            DynamicMenu::where('title', 'Input Non-Akademik')
                        ->where('url', '#')
                        ->delete();
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
        // No down needed, this repairs data
    }
}
