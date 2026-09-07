<?php

namespace App\Providers;

use App\Models\Module;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Comparte la configuración del sitio y el menú con todas las vistas del frontend.
        try {
            if (Schema::hasTable('site_settings')) {
                View::share('settings', SiteSetting::current());

                View::share('menuPages', Page::query()
                    ->where('is_published', true)
                    ->where('show_in_menu', true)
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->get());
            }

            if (Schema::hasTable('modules')) {
                View::share('menuModules', Module::query()
                    ->where('is_public', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get());
            }
        } catch (\Throwable $e) {
            // Antes de migrar la base de datos, simplemente se omite.
        }
    }
}
