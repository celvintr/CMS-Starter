<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\AjustesSitio;
use App\Filament\Pages\FuncionesSitio;
use App\Filament\Resources\MailAccountResource;
use App\Filament\Resources\ModuleResource;
use App\Filament\Resources\PageResource;
use App\Models\MailAccount;
use App\Models\Module;
use App\Models\Page;
use App\Models\SiteSetting;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Schema;

class PrimerosPasos extends Widget
{
    protected static string $view = 'filament.widgets.primeros-pasos';

    protected static ?int $sort = -1; // arriba de todo

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin() || ! Schema::hasTable('site_settings')) {
            return false;
        }

        if (SiteSetting::current()->onboarding_dismissed_at) {
            return false;
        }

        return ! collect(static::steps())->every(fn ($s) => $s['done']);
    }

    /**
     * @return array<int, array{label:string, description:string, done:bool, url:string, icon:string}>
     */
    public static function steps(): array
    {
        $s = SiteSetting::current();

        return [
            [
                'label' => 'Configura los datos de tu sitio',
                'description' => 'Nombre, contacto, redes y SEO.',
                'done' => ! empty($s->site_name) && $s->site_name !== 'Mi Sitio',
                'url' => AjustesSitio::getUrl(),
                'icon' => 'heroicon-o-cog-6-tooth',
            ],
            [
                'label' => 'Sube tu logo y favicon',
                'description' => 'Tu marca en el sitio y en el panel.',
                'done' => ! empty($s->logo_path),
                'url' => AjustesSitio::getUrl(),
                'icon' => 'heroicon-o-photo',
            ],
            [
                'label' => 'Activa las funciones que usarás',
                'description' => 'Tienda, multilenguaje, newsletter, reservas…',
                'done' => ! empty($s->features),
                'url' => FuncionesSitio::getUrl(),
                'icon' => 'heroicon-o-puzzle-piece',
            ],
            [
                'label' => 'Crea tu primer módulo',
                'description' => 'Productos, servicios, equipo… tu contenido.',
                'done' => Schema::hasTable('modules') && Module::exists(),
                'url' => ModuleResource::getUrl('index'),
                'icon' => 'heroicon-o-squares-2x2',
            ],
            [
                'label' => 'Crea tu primera página',
                'description' => 'Arma una página con bloques.',
                'done' => Schema::hasTable('pages') && Page::exists(),
                'url' => PageResource::getUrl('index'),
                'icon' => 'heroicon-o-document-text',
            ],
            [
                'label' => 'Conecta un correo (SMTP)',
                'description' => 'Para notificaciones y campañas.',
                'done' => Schema::hasTable('mail_accounts') && MailAccount::exists(),
                'url' => MailAccountResource::getUrl('index'),
                'icon' => 'heroicon-o-envelope',
            ],
        ];
    }

    public function dismiss(): void
    {
        SiteSetting::current()->update(['onboarding_dismissed_at' => now()]);
    }

    protected function getViewData(): array
    {
        $steps = static::steps();
        $done = collect($steps)->where('done', true)->count();

        return [
            'steps' => $steps,
            'doneCount' => $done,
            'total' => count($steps),
        ];
    }
}
