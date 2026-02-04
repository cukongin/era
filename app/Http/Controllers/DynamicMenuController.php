<?php

namespace App\Http\Controllers;

use App\Models\DynamicMenu;
use App\Models\MenuRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\DynamicPage;

use Illuminate\Support\Facades\Route;

class DynamicMenuController extends Controller
{
    public function index()
    {
        $menus = DynamicMenu::with(['children', 'roles'])
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();
            
        $pages = DynamicPage::where('status', 'published')->orderBy('title')->get();

        // Get Available Routes
        // Get Available Routes & Format them
        // Get Available Routes & Format them
        $allRoutes = collect(Route::getRoutes()->getRoutesByName())
            ->filter(function ($value, $key) {
                return in_array('GET', $value->methods()) && 
                       !str_starts_with($key, '_ignition') && 
                       !str_starts_with($key, 'ignition') && 
                       !str_starts_with($key, 'sanctum') &&
                       !str_starts_with($key, 'api') &&
                       !str_starts_with($key, 'livewire') &&
                       !str_contains($value->uri(), '{'); // Exclude parameterized routes
            })
            ->keys()
            ->sort();

        $routes = [];
        foreach ($allRoutes as $route) {
            $parts = explode('.', $route);
            $group = ucfirst($parts[0]);
            
            // Custom Labels Mapping
            $label = $route;
            if ($route === 'dashboard') $label = 'Dashboard Utama';
            if ($route === 'profile.edit') $label = 'Profil Pengguna';
            if (str_contains($route, 'index')) $label = 'Halaman Daftar / Utama';
            if (str_contains($route, 'create')) $label = 'Form Tambah Data';
            if (str_contains($route, 'edit')) $label = 'Form Edit Data';
            if (str_contains($route, 'show')) $label = 'Detail Data';
            
            // Humanize Group Name
            if ($group === 'Settings') $group = 'Pengaturan';
            if ($group === 'Master') $group = 'Data Master';
            if ($group === 'Teacher') $group = 'Guru';
            if ($group === 'Walikelas') $group = 'Wali Kelas';
            if ($group === 'Classes') $group = 'Kelas';

            $routes[$group][] = [
                'name' => $route,
                'label' => $label
            ];
        }
            
        return view('settings.menus.index', compact('menus', 'pages', 'routes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'order' => 'required|integer',
            'roles' => 'required|array',
            'icon' => 'nullable|string',
            'route' => 'nullable|string',
            'url' => 'nullable|string',
            'parent_id' => 'nullable|exists:dynamic_menus,id'
        ]);

        DB::transaction(function () use ($request) {
            $menu = DynamicMenu::create($request->only([
                'title', 'icon', 'route', 'url', 'parent_id', 'order', 'is_active'
            ]));

            foreach ($request->roles as $role) {
                MenuRole::create([
                    'menu_id' => $menu->id,
                    'role' => $role
                ]);
            }
        });

        return back()->with('success', 'Menu berhasil ditambahkan');
    }

    public function update(Request $request, DynamicMenu $menu)
    {
        $request->validate([
            'title' => 'required',
            'order' => 'required|integer',
            'roles' => 'required|array',
        ]);

        DB::transaction(function () use ($request, $menu) {
            $menu->update($request->only([
                'title', 'icon', 'route', 'url', 'parent_id', 'order', 'is_active'
            ]));

            // Sync Roles
            MenuRole::where('menu_id', $menu->id)->delete();
            foreach ($request->roles as $role) {
                MenuRole::create([
                    'menu_id' => $menu->id,
                    'role' => $role
                ]);
            }
        });

        return back()->with('success', 'Menu berhasil diperbarui');
    }

    public function destroy(DynamicMenu $menu)
    {
        $menu->delete();
        return back()->with('success', 'Menu berhasil dihapus');
    }
}
