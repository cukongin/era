<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

class RestructureWalikelasMenus extends Migration
{
    public function up()
    {
        // 1. Create or Update Parent "Manajemen Wali Kelas"
        // Try to find existing 'Wali Kelas' or create new
        $parent = DynamicMenu::where('title', 'Wali Kelas')->orWhere('title', 'Manajemen Wali Kelas')->first();
        
        if (!$parent) {
            $parent = DynamicMenu::create([
                'title' => 'Manajemen Wali Kelas',
                'icon' => 'supervisor_account',
                'order' => 4,
                'is_active' => true
            ]);
        } else {
            // Update title to be clear
            $parent->update([
                'title' => 'Manajemen Wali Kelas',
                'icon' => 'supervisor_account',
                'is_active' => true
            ]);
        }
        
        // Ensure roles for parent
        $this->assignRoles($parent, ['admin', 'walikelas']);


        // 2. Define Children Menus
        $children = [
            [
                'title' => 'Dashboard Wali',
                'route' => 'walikelas.dashboard',
                'icon' => 'dashboard',
                'order' => 1
            ],
            [
                'title' => 'Absensi & Kepribadian',
                'route' => 'walikelas.absensi',
                'icon' => 'fact_check',  // Ceklist
                'order' => 2
            ],
            [
                'title' => 'Ekstrakurikuler',
                'route' => 'ekskul.index',
                'icon' => 'sports_soccer', // Bola
                'order' => 3
            ],
            [
                'title' => 'Catatan Siswa',
                'route' => 'walikelas.catatan.index',
                'icon' => 'rate_review', // Pena/Review
                'order' => 4
            ],
            [
                'title' => 'Monitoring Nilai',
                'route' => 'walikelas.monitoring',
                'icon' => 'analytics', // Grafik
                'order' => 5
            ],
            [
                'title' => 'Leger Nilai',
                'route' => 'walikelas.leger',
                'icon' => 'table_view', // Tabel
                'order' => 6
            ],
            [
                'title' => 'Kenaikan Kelas',
                'route' => 'walikelas.kenaikan.index',
                'icon' => 'upgrade', // Panah atas
                'order' => 7
            ],
            [
                'title' => 'Cetak Rapor',
                'route' => 'reports.index',
                'icon' => 'print', // Printer
                'order' => 8
            ]
        ];

        foreach ($children as $childData) {
            // Check existence by route to avoid duplicates
            // OR check by title if route changed. 
            // Better to check by route as it is unique identifier for functionality.
            $menu = DynamicMenu::where('route', $childData['route'])->first();
            
            if (!$menu) {
                // If not found by route, check by Title to rename/repurpose?
                $menu = DynamicMenu::where('title', $childData['title'])->where('parent_id', $parent->id)->first();
            }

            if ($menu) {
                // Update existing
                $menu->update([
                    'title' => $childData['title'], // Ensure title is standard
                    'parent_id' => $parent->id, // Move to this parent
                    'icon' => $childData['icon'],
                    'order' => $childData['order'],
                    'url' => null, // clear url if route used
                    'is_active' => true
                ]);
            } else {
                // Create new
                $menu = DynamicMenu::create([
                    'title' => $childData['title'],
                    'route' => $childData['route'],
                    'icon' => $childData['icon'],
                    'parent_id' => $parent->id,
                    'order' => $childData['order'],
                    'is_active' => true
                ]);
            }

            $this->assignRoles($menu, ['admin', 'walikelas']);
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
        // No down needed
    }
}
