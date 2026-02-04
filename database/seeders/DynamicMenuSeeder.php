<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DynamicMenu;
use App\Models\MenuRole;

class DynamicMenuSeeder extends Seeder
{
    public function run()
    {
        // 1. Dashboard (All)
        $dashboard = DynamicMenu::create([
            'title' => 'Dashboard',
            'icon' => 'dashboard',
            'url' => '/',
            'order' => 1
        ]);
        $this->assignRoles($dashboard, ['admin', 'teacher', 'walikelas']);
        
        // 2. Data Utama (Admin Only)
        $master = DynamicMenu::create([
            'title' => 'Data Utama',
            'icon' => 'database',
            'order' => 2
        ]);
        $this->assignRoles($master, ['admin']);

        // Submenus Master
        $this->createSubMenu($master, 'Data Siswa', 'people', 'master.students.index', 1, ['admin']);
        $this->createSubMenu($master, 'Data Guru', 'school', 'master.teachers.index', 2, ['admin']);
        $this->createSubMenu($master, 'Data Mapel', 'menu_book', 'master.mapel.index', 3, ['admin']);
        $this->createSubMenu($master, 'Manajemen Kelas', 'meeting_room', 'classes.index', 4, ['admin']);

        // 3. Menu Guru (Teacher & Admin)
        $teacher = DynamicMenu::create([
            'title' => 'Menu Guru',
            'icon' => 'school',
            'order' => 3
        ]);
        $this->assignRoles($teacher, ['admin', 'teacher']);
        
        $this->createSubMenu($teacher, 'Input Nilai', 'edit_note', 'teacher.dashboard', 1, ['admin', 'teacher']);
        $this->createSubMenu($teacher, 'Jadwal Mengajar', 'calendar_month', '#', 2, ['admin', 'teacher']);

        // 4. Menu Wali Kelas (Wali Kelas & Admin)
        $wali = DynamicMenu::create([
            'title' => 'Wali Kelas',
            'icon' => 'supervisor_account',
            'order' => 4
        ]);
        $this->assignRoles($wali, ['admin', 'walikelas']);

        $this->createSubMenu($wali, 'Dashboard Wali', 'dashboard', 'walikelas.dashboard', 1, ['admin', 'walikelas']);
        $this->createSubMenu($wali, 'Input Non-Akademik', 'playlist_add_check', '#', 2, ['admin', 'walikelas']);
        $this->createSubMenu($wali, 'Monitoring Nilai', 'analytics', 'walikelas.monitoring', 3, ['admin', 'walikelas']);
        $this->createSubMenu($wali, 'Cetak Rapor', 'print', 'reports.index', 4, ['admin', 'walikelas']);

        // 5. Pengaturan (Admin Only)
        $settings = DynamicMenu::create([
            'title' => 'Pengaturan',
            'icon' => 'settings',
            'order' => 99
        ]);
        $this->assignRoles($settings, ['admin']);

        $this->createSubMenu($settings, 'Identitas Sekolah', 'domain', 'settings.school', 1, ['admin']);
        $this->createSubMenu($settings, 'Aturan Penilaian', 'tune', 'settings.grading', 2, ['admin']);
        $this->createSubMenu($settings, 'Manajemen User', 'manage_accounts', 'settings.users.index', 3, ['admin']);

        // 6. Pusat Informasi (New Feature - All)
        $info = DynamicMenu::create([
            'title' => 'Pusat Informasi',
            'icon' => 'info',
            'order' => 5
        ]);
        $this->assignRoles($info, ['admin', 'teacher', 'walikelas']);
        
        $this->createSubMenu($info, 'Panduan Aplikasi', 'help', '#', 1, ['admin', 'teacher', 'walikelas']);
    }

    private function createSubMenu($parent, $title, $icon, $route, $order, $roles)
    {
        $menu = DynamicMenu::create([
            'title' => $title,
            'icon' => $icon,
            'route' => $route == '#' ? null : $route,
            'url' => $route == '#' ? '#' : null,
            'parent_id' => $parent->id,
            'order' => $order
        ]);
        $this->assignRoles($menu, $roles);
    }

    private function assignRoles($menu, $roles)
    {
        foreach ($roles as $role) {
            MenuRole::create([
                'menu_id' => $menu->id,
                'role' => $role
            ]);
        }
    }
}
