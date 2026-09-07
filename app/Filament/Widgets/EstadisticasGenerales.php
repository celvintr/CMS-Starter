<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Entry;
use App\Models\Module;
use App\Models\Page;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class EstadisticasGenerales extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $mensajesNuevos = ContactMessage::whereNull('read_at')->count();

        // Mini-gráfica: mensajes de los últimos 7 días.
        $sparkline = collect(range(6, 0))
            ->map(fn ($i) => ContactMessage::whereDate('created_at', Carbon::today()->subDays($i))->count())
            ->all();

        return [
            Stat::make('Páginas', Page::count())
                ->description(Page::where('is_published', true)->count() . ' publicadas')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Módulos', Module::count())
                ->description('Tipos de contenido')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),

            Stat::make('Registros', Entry::count())
                ->description('Contenido en módulos')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('success'),

            Stat::make('Mensajes nuevos', $mensajesNuevos)
                ->description(ContactMessage::count() . ' en total')
                ->descriptionIcon('heroicon-m-inbox')
                ->chart($sparkline)
                ->color($mensajesNuevos > 0 ? 'warning' : 'gray'),
        ];
    }
}
