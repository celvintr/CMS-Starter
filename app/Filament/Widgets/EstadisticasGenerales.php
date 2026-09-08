<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Entry;
use App\Models\Order;
use App\Models\Page;
use App\Models\Reservation;
use App\Models\SiteSetting;
use App\Models\Subscriber;
use App\Support\Features;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class EstadisticasGenerales extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stats = [
            Stat::make('Páginas', Page::count())
                ->description(Page::published()->count() . ' publicadas')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Registros', Entry::count())
                ->description('Contenido en módulos')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('info'),
        ];

        // Tienda: ventas del mes y órdenes pendientes.
        if (Features::enabled('tienda') && Schema::hasTable('orders')) {
            $currency = strtoupper(SiteSetting::current()->currency ?: 'USD');

            $ventasMes = (float) Order::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total');

            $ventasSparkline = collect(range(6, 0))
                ->map(fn ($i) => (float) Order::where('status', 'paid')
                    ->whereDate('paid_at', Carbon::today()->subDays($i))
                    ->sum('total'))
                ->all();

            $pendientes = Order::where('status', 'pending')->count();

            $stats[] = Stat::make('Ventas del mes', number_format($ventasMes, 2) . ' ' . $currency)
                ->description('Órdenes pagadas este mes')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($ventasSparkline)
                ->color('success');

            $stats[] = Stat::make('Órdenes pendientes', $pendientes)
                ->description($pendientes > 0 ? 'Requieren seguimiento' : 'Todo al día')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color($pendientes > 0 ? 'warning' : 'gray');
        }

        // Reservas próximas (no canceladas, de hoy en adelante).
        if (Features::enabled('reservas') && Schema::hasTable('reservations')) {
            $proximas = Reservation::where('status', '!=', 'canceled')
                ->where('starts_at', '>=', now()->startOfDay())
                ->count();

            $stats[] = Stat::make('Reservas próximas', $proximas)
                ->description(Reservation::where('status', 'pending')->count() . ' por confirmar')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($proximas > 0 ? 'primary' : 'gray');
        }

        // Newsletter: suscriptores activos.
        if (Features::enabled('newsletter') && Schema::hasTable('subscribers')) {
            $activos = Subscriber::where('is_active', true)->count();
            $nuevos = Subscriber::where('created_at', '>=', now()->subDays(7))->count();

            $stats[] = Stat::make('Suscriptores', $activos)
                ->description($nuevos . ' nuevos esta semana')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('success');
        }

        // Mensajes de contacto (siempre).
        $mensajesNuevos = ContactMessage::whereNull('read_at')->count();
        $sparkline = collect(range(6, 0))
            ->map(fn ($i) => ContactMessage::whereDate('created_at', Carbon::today()->subDays($i))->count())
            ->all();

        $stats[] = Stat::make('Mensajes nuevos', $mensajesNuevos)
            ->description(ContactMessage::count() . ' en total')
            ->descriptionIcon('heroicon-m-inbox')
            ->chart($sparkline)
            ->color($mensajesNuevos > 0 ? 'warning' : 'gray');

        return $stats;
    }
}
