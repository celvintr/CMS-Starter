<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use App\Models\Module;
use App\Support\Features;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Cupones';
    protected static ?string $modelLabel = 'cupón';
    protected static ?string $pluralModelLabel = 'cupones';
    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return Features::enabled('tienda');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Cupón')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Código')
                        ->required()
                        ->maxLength(40)
                        ->unique(ignoreRecord: true)
                        ->dehydrateStateUsing(fn (string $state): string => strtoupper(trim($state)))
                        ->helperText('Lo que el cliente escribe en el carrito. Ej: BIENVENIDA10.'),
                    Forms\Components\Select::make('module_id')
                        ->label('Tienda')
                        ->options(fn () => Module::where('type', 'tienda')->pluck('name', 'id')->all())
                        ->native(false)
                        ->placeholder('Todas las tiendas')
                        ->helperText('Limita el cupón a una tienda. Vacío = aplica a cualquier producto.'),
                    Forms\Components\Select::make('type')
                        ->label('Tipo de descuento')
                        ->options(['percent' => 'Porcentaje (%)', 'fixed' => 'Monto fijo'])
                        ->default('percent')
                        ->native(false)
                        ->required()
                        ->live(),
                    Forms\Components\TextInput::make('value')
                        ->label(fn (Forms\Get $get) => $get('type') === 'fixed' ? 'Monto a descontar' : 'Porcentaje a descontar')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->suffix(fn (Forms\Get $get) => $get('type') === 'fixed' ? null : '%'),
                ])->columns(2),

            Forms\Components\Section::make('Reglas y límites')
                ->schema([
                    Forms\Components\Toggle::make('active')->label('Activo')->default(true),
                    Forms\Components\DateTimePicker::make('expires_at')
                        ->label('Vence el')
                        ->native(false)
                        ->helperText('Vacío = sin fecha de vencimiento.'),
                    Forms\Components\TextInput::make('min_total')
                        ->label('Compra mínima')
                        ->numeric()->minValue(0)
                        ->helperText('El subtotal debe alcanzar este monto. Opcional.'),
                    Forms\Components\TextInput::make('max_uses')
                        ->label('Usos máximos')
                        ->numeric()->minValue(1)
                        ->helperText('Cuántas veces puede canjearse en total. Vacío = ilimitado.'),
                    Forms\Components\Placeholder::make('uses')
                        ->label('Usos hasta ahora')
                        ->content(fn (?Coupon $record) => (string) ($record?->uses ?? 0))
                        ->visibleOn('edit'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Código')->searchable()->weight('bold')->copyable(),
                Tables\Columns\TextColumn::make('module.name')->label('Tienda')->badge()->color('gray')->placeholder('Todas'),
                Tables\Columns\TextColumn::make('value')->label('Descuento')
                    ->formatStateUsing(fn ($state, Coupon $record) => $record->type === 'percent'
                        ? rtrim(rtrim(number_format((float) $state, 2), '0'), '.') . '%'
                        : number_format((float) $state, 2)),
                Tables\Columns\IconColumn::make('active')->label('Activo')->boolean(),
                Tables\Columns\TextColumn::make('uses')->label('Usos')
                    ->formatStateUsing(fn ($state, Coupon $record) => $record->max_uses ? "{$state} / {$record->max_uses}" : (string) $state),
                Tables\Columns\TextColumn::make('expires_at')->label('Vence')->date('d/m/Y')->placeholder('—')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('active')->label('Activo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
