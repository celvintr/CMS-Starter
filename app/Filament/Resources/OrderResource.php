<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\MailAccount;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Support\EmailTemplate;
use App\Support\Features;
use App\Support\Mailer;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
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

    public static function canAccess(): bool
    {
        return Features::enabled('tienda');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    protected static function statusColor(string $state): string
    {
        return match ($state) {
            'paid' => 'success',
            'shipped' => 'info',
            'delivered' => 'success',
            'canceled' => 'danger',
            default => 'warning',
        };
    }

    protected static function statusLabel(string $state): string
    {
        return Order::statuses()[$state] ?? $state;
    }

    /**
     * Avisa al cliente por correo (cuenta del canal de órdenes) sin romper la acción.
     */
    protected static function notifyCustomer(Order $order, string $subject, string $message): void
    {
        try {
            $settings = SiteSetting::current();
            $account = MailAccount::find($settings->notify_orders_account_id);

            if (! $account) {
                return;
            }

            $body = '<p>Hola ' . e($order->customer_name) . ',</p><p>' . e($message) . '</p>'
                . '<p><strong>Referencia:</strong> ' . e($order->reference) . '</p>'
                . ($order->shipping_address ? '<p><strong>Envío a:</strong> ' . e($order->shipping_address) . '</p>' : '');

            Mailer::send($account, $order->customer_email, $subject . ' — ' . $settings->site_name, EmailTemplate::render($subject, $body));
        } catch (\Throwable $e) {
            // silencioso
        }
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
                Tables\Filters\SelectFilter::make('status')->label('Estado')->options(Order::statuses()),
            ])
            ->actions([
                Tables\Actions\Action::make('enviar')
                    ->label('Marcar enviada')->icon('heroicon-o-truck')->color('info')
                    ->visible(fn (Order $record) => $record->status === 'paid')
                    ->requiresConfirmation()
                    ->modalDescription('Se marcará como enviada y se avisará al cliente por correo (si hay cuenta configurada).')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'shipped']);
                        static::notifyCustomer($record, 'Tu pedido va en camino', 'Tu pedido fue enviado.');
                        Notification::make()->title('Orden marcada como enviada.')->success()->send();
                    }),
                Tables\Actions\Action::make('entregar')
                    ->label('Marcar entregada')->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn (Order $record) => $record->status === 'shipped')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->update(['status' => 'delivered']);
                        static::notifyCustomer($record, 'Tu pedido fue entregado', 'Marcamos tu pedido como entregado. ¡Gracias por tu compra!');
                        Notification::make()->title('Orden marcada como entregada.')->success()->send();
                    }),
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
                Infolists\Components\TextEntry::make('coupon_code')->label('Cupón')->placeholder('—')
                    ->visible(fn (Order $record) => ! empty($record->coupon_code)),
                Infolists\Components\TextEntry::make('discount')->label('Descuento')
                    ->formatStateUsing(fn ($state, Order $record) => '−' . number_format((float) $state, 2) . ' ' . strtoupper($record->currency))
                    ->visible(fn (Order $record) => (float) $record->discount > 0),
                Infolists\Components\TextEntry::make('shipping')->label('Envío')
                    ->formatStateUsing(fn ($state, Order $record) => number_format((float) $state, 2) . ' ' . strtoupper($record->currency))
                    ->visible(fn (Order $record) => (float) $record->shipping > 0),
            ])->columns(2),

            Infolists\Components\Section::make('Cliente')->schema([
                Infolists\Components\TextEntry::make('customer_name')->label('Nombre'),
                Infolists\Components\TextEntry::make('customer_email')->label('Correo')->placeholder('—'),
                Infolists\Components\TextEntry::make('customer_phone')->label('Teléfono')->placeholder('—'),
                Infolists\Components\TextEntry::make('shipping_address')->label('Dirección de envío')->placeholder('—')
                    ->visible(fn (Order $record) => ! empty($record->shipping_address))
                    ->columnSpanFull(),
            ])->columns(3),

            Infolists\Components\Section::make('Productos')->schema([
                Infolists\Components\RepeatableEntry::make('items')->label('')->schema([
                    Infolists\Components\TextEntry::make('title')->label('Producto'),
                    Infolists\Components\TextEntry::make('variant')->label('Opción')->placeholder('—'),
                    Infolists\Components\TextEntry::make('qty')->label('Cantidad'),
                    Infolists\Components\TextEntry::make('subtotal')->label('Subtotal')
                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                ])->columns(4),
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
