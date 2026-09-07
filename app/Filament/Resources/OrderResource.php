<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Órdenes';
    protected static ?string $modelLabel = 'orden';
    protected static ?string $pluralModelLabel = 'órdenes';
    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    protected static function statusColor(string $state): string
    {
        return match ($state) {
            'paid' => 'success',
            'canceled' => 'danger',
            default => 'warning',
        };
    }

    protected static function statusLabel(string $state): string
    {
        return ['pending' => 'Pendiente', 'paid' => 'Pagada', 'canceled' => 'Cancelada'][$state] ?? $state;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('Referencia')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('customer_name')->label('Cliente')->searchable(),
                Tables\Columns\TextColumn::make('total')->label('Total')->sortable()
                    ->formatStateUsing(fn ($state, Order $record) => number_format((float) $state, 2) . ' ' . strtoupper($record->currency)),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()
                    ->color(fn (string $state) => static::statusColor($state))
                    ->formatStateUsing(fn (string $state) => static::statusLabel($state)),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Estado')
                    ->options(['pending' => 'Pendiente', 'paid' => 'Pagada', 'canceled' => 'Cancelada']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Ver'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Orden')->schema([
                Infolists\Components\TextEntry::make('reference')->label('Referencia'),
                Infolists\Components\TextEntry::make('status')->label('Estado')->badge()
                    ->color(fn (string $state) => static::statusColor($state))
                    ->formatStateUsing(fn (string $state) => static::statusLabel($state)),
                Infolists\Components\TextEntry::make('total')->label('Total')
                    ->formatStateUsing(fn ($state, Order $record) => number_format((float) $state, 2) . ' ' . strtoupper($record->currency)),
                Infolists\Components\TextEntry::make('paid_at')->label('Pagada')->dateTime('d/m/Y H:i')->placeholder('—'),
            ])->columns(2),

            Infolists\Components\Section::make('Cliente')->schema([
                Infolists\Components\TextEntry::make('customer_name')->label('Nombre'),
                Infolists\Components\TextEntry::make('customer_email')->label('Correo')->placeholder('—'),
                Infolists\Components\TextEntry::make('customer_phone')->label('Teléfono')->placeholder('—'),
            ])->columns(3),

            Infolists\Components\Section::make('Productos')->schema([
                Infolists\Components\RepeatableEntry::make('items')->label('')->schema([
                    Infolists\Components\TextEntry::make('title')->label('Producto'),
                    Infolists\Components\TextEntry::make('qty')->label('Cantidad'),
                    Infolists\Components\TextEntry::make('subtotal')->label('Subtotal')
                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                ])->columns(3),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }
}
