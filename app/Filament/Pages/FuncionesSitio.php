<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\Features;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class FuncionesSitio extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $navigationLabel = 'Funciones del sitio';
    protected static ?int $navigationSort = 88;
    protected static string $view = 'filament.pages.funciones-sitio';

    public function getTitle(): string
    {
        return 'Funciones del sitio';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function toggle(string $key): void
    {
        $catalog = Features::catalog();
        if (! isset($catalog[$key])) {
            return;
        }

        $settings = SiteSetting::current();
        $features = $settings->features ?? [];
        $current = array_key_exists($key, $features)
            ? (bool) $features[$key]
            : ($catalog[$key]['default'] ?? false);

        $features[$key] = ! $current;
        $settings->update(['features' => $features]);
        Features::flush();

        Notification::make()
            ->title($catalog[$key]['name'] . ($features[$key] ? ' activada' : ' desactivada'))
            ->success()
            ->send();
    }
}
