<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ReservationResource;
use App\Models\Reservation;
use App\Support\Features;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Schema;

class ProximasReservas extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Próximas reservas';

    public static function canView(): bool
    {
        return Features::enabled('reservas') && Schema::hasTable('reservations');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
                    ->where('status', '!=', 'canceled')
                    ->where('starts_at', '>=', now()->startOfDay())
                    ->orderBy('starts_at')
            )
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Cliente')->weight('bold'),
                Tables\Columns\TextColumn::make('starts_at')->label('Fecha y hora')->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('service')->label('Servicio')->placeholder('—')->limit(30),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()
                    ->color(fn (string $state) => $state === 'confirmed' ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state) => Reservation::statuses()[$state] ?? $state),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->label('Ver todas')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn () => ReservationResource::getUrl('index')),
            ])
            ->emptyStateHeading('Sin reservas próximas')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }
}
