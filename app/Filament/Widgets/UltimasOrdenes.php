<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Support\Features;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Schema;

class UltimasOrdenes extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Órdenes recientes';

    public static function canView(): bool
    {
        return Features::enabled('tienda') && Schema::hasTable('orders');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest())
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('Referencia')->weight('bold'),
                Tables\Columns\TextColumn::make('customer_name')->label('Cliente'),
                Tables\Columns\TextColumn::make('total')->label('Total')
                    ->formatStateUsing(fn ($state, Order $record) => number_format((float) $state, 2) . ' ' . strtoupper($record->currency)),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()
                    ->color(fn (string $state) => ['paid' => 'success', 'canceled' => 'danger'][$state] ?? 'warning')
                    ->formatStateUsing(fn (string $state) => ['pending' => 'Pendiente', 'paid' => 'Pagada', 'canceled' => 'Cancelada'][$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->label('Ver todas')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn () => OrderResource::getUrl('index')),
            ])
            ->emptyStateHeading('Sin órdenes todavía')
            ->emptyStateIcon('heroicon-o-shopping-cart');
    }
}
