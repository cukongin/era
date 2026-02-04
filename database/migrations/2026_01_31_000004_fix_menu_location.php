<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DynamicMenu;

class FixMenuLocation extends Migration
{
    public function up()
    {
        // Fix Parent Location
        $parent = DynamicMenu::where('title', 'Manajemen Wali Kelas')->first();
        if ($parent) {
            $parent->update(['location' => 'sidebar']);
        }

        // Also ensure Children locations if logic requires (though getSidebar fetches roots only usually)
        // But let's check getSidebar children eager load:
        // ->with(['children' => function($q) { $q->where('is_active', true)->orderBy('order'); }
        // It does NOT filter children by location, so fixing parent is enough.
        // But for consistency, let's fix children ? Or maybe sidebar logic only checks root location.
        // Line 30: whereNull('parent_id')->where('location', 'sidebar') --> Root must be in sidebar.
    }

    public function down()
    {
    }
}
