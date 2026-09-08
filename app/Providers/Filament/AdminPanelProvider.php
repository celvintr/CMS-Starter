<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\SiteSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Schema;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Marca del cliente (nombre, logo, favicon, color) desde los ajustes del sitio.
        // Lectura defensiva: durante migraciones la tabla puede no existir todavía.
        $settings = null;
        try {
            $settings = Schema::hasTable('site_settings') ? SiteSetting::query()->first() : null;
        } catch (\Throwable $e) {
            $settings = null;
        }

        $brandColor = $settings?->primary_color;
        $logoUrl = $settings?->logo_path ? asset('storage/' . $settings->logo_path) : null;
        $faviconUrl = $settings?->favicon_path ? asset('storage/' . $settings->favicon_path) : null;

        $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(isSimple: false)
            ->brandName($settings?->site_name ?: 'CMS Starter')
            ->colors([
                'primary' => $brandColor ? Color::hex($brandColor) : Color::Blue,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                RequireTwoFactor::class,
            ]);

        if ($logoUrl) {
            $panel->brandLogo($logoUrl)->brandLogoHeight('2rem');
        }

        if ($faviconUrl) {
            $panel->favicon($faviconUrl);
        }

        return $panel;
    }
}
