<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Dynamic Menus Table
        Schema::create('dynamic_menus', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('icon')->nullable(); // Material Symbols
            $table->string('route')->nullable(); // Route name
            $table->string('url')->nullable(); // Static URL or internal path
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('location')->default('sidebar'); // sidebar, topbar, footer
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('dynamic_menus')->onDelete('cascade');
        });

        // 2. Menu Roles Table (Permission)
        Schema::create('menu_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->string('role'); // admin, teacher, walikelas, student
            
            $table->foreign('menu_id')->references('id')->on('dynamic_menus')->onDelete('cascade');
        });

        // 3. Dynamic Pages Table (CMS)
        Schema::create('dynamic_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dynamic_pages');
        Schema::dropIfExists('menu_roles');
        Schema::dropIfExists('dynamic_menus');
    }
};
