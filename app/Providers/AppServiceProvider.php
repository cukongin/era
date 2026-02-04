<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Set Carbon Locale
        \Carbon\Carbon::setLocale('id');

        // Inject Dynamic Menus to Sidebar
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
             if (\Illuminate\Support\Facades\Auth::check()) {
                $view->with('sidebarMenus', \App\Models\DynamicMenu::getSidebar());
             }
        });
    }
}
