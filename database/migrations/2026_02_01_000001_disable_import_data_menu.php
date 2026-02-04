<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DynamicMenu;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Disable "Import Data" and "Absensi & Sikap"
        // Based on ID investigation: 38 and 39
        // Using title search to be safe across environments
        DynamicMenu::where('title', 'Import Data')->update(['is_active' => false]);
        DynamicMenu::where('title', 'Absensi & Sikap')->update(['is_active' => false]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DynamicMenu::where('title', 'Import Data')->update(['is_active' => true]);
        DynamicMenu::where('title', 'Absensi & Sikap')->update(['is_active' => true]);
    }
};
