<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicMenu extends Model
{
    protected $guarded = ['id'];

    public function children()
    {
        return $this->hasMany(DynamicMenu::class, 'parent_id')->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(DynamicMenu::class, 'parent_id');
    }

    public function roles()
    {
        return $this->hasMany(MenuRole::class, 'menu_id');
    }

    // Helper to check if user has permission
    public static function getSidebar()
    {
        // For now return all active root menus, we filter by role in View Composer or Blade
        return self::whereNull('parent_id')
            ->where('is_active', true)
            ->where('location', 'sidebar')
            ->orderBy('order')
            ->with(['children' => function($q) {
                $q->where('is_active', true)->orderBy('order');
            }, 'roles'])
            ->get();
    }

    public function getSafeUrl()
    {
        if ($this->route) {
            try {
                return route($this->route);
            } catch (\Exception $e) {
                return '#error-route';
            }
        }
        return url($this->url ?? '#');
    }
}
