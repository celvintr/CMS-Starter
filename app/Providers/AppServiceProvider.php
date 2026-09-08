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
        // Datos compartidos con todas las vistas del frontend (config del sitio + menú).
        // Se resuelven una vez por petición (singleton) y de forma PEREZOSA en el
        // render, no en el boot: así la base ya está migrada (importante en tests) y
        // los cambios de ajustes se reflejan sin recachear.
        $this->app->singleton('cms.viewdata', function () {
            try {
                if (! Schema::hasTable('site_settings')) {
                    return ['settings' => null, 'menuPages' => collect(), 'menuModules' => collect()];
                }

                return [
                    'settings' => SiteSetting::current(),
                    'menuPages' => Page::query()
                        ->published()
                        ->where('show_in_menu', true)
                        ->orderBy('sort_order')
                        ->orderBy('title')
                        ->get(),
                    'menuModules' => Schema::hasTable('modules')
                        ? Module::query()->where('is_public', true)->orderBy('sort_order')->orderBy('name')->get()
                        : collect(),
                ];
            } catch (\Throwable $e) {
                return ['settings' => null, 'menuPages' => collect(), 'menuModules' => collect()];
            }
        });

        View::composer('*', function ($view) {
            foreach (app('cms.viewdata') as $key => $value) {
                $view->with($key, $value);
            }
        });
    }
}
