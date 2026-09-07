<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Models\MailAccount;
use App\Models\Reservation;
use App\Models\SiteSetting;
use App\Support\Features;
use App\Support\Mailer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Reservas';
    protected static ?string $navigationLabel = 'Citas';
    protected static ?string $modelLabel = 'reserva';
    protected static ?string $pluralModelLabel = 'reservas';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Features::enabled('reservas');
    }

    public static function getNavigationBadge(): ?string
    {
        if (! Features::enabled('reservas')) {
            return null;
        }

        $pending = static::getModel()::where('status', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos de la reserva')->schema([
                Forms\Components\TextInput::make('name')->label('Nombre')->required(),
                Forms\Components\TextInput::make('email')->label('Correo')->email()->required(),
                Forms\Components\TextInput::make('phone')->label('Teléfono'),
                Forms\Components\TextInput::make('service')->label('Servicio'),
                Forms\Components\DateTimePicker::make('starts_at')->label('Fecha y hora')->native(false)->required()->seconds(false),
                Forms\Components\Select::make('status')->label('Estado')
                    ->options(Reservation::statuses())->default('pending')->native(false)->required(),
                Forms\Components\Textarea::make('notes')->label('Notas')->rows(3)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Cliente')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('starts_at')->label('Fecha y hora')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('service')->label('Servicio')->placeholder('—')->limit(30),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'confirmed' => 'success',
                        'canceled' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state) => Reservation::statuses()[$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')->label('Solicitada')->since()->sortable(),
            ])
            ->defaultSort('starts_at', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Estado')->options(Reservation::statuses()),
            ])
            ->actions([
                Tables\Actions\Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Reservation $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalDescription('Se marcará como confirmada y se avisará al cliente por correo (si hay una cuenta configurada).')
                    ->action(function (Reservation $record) {
                        $record->update(['status' => 'confirmed']);
                        static::notifyCustomer($record, 'Tu reserva fue confirmada', 'Confirmamos tu reserva para el ' . $record->starts_at->format('d/m/Y H:i') . '.');
                        Notification::make()->title('Reserva confirmada.')->success()->send();
                    }),
                Tables\Actions\Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Reservation $record) => $record->status !== 'canceled')
                    ->requiresConfirmation()
                    ->action(function (Reservation $record) {
                        $record->update(['status' => 'canceled']);
                        Notification::make()->title('Reserva cancelada.')->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Avisa al cliente por correo usando la cuenta del canal de formularios.
     * Nunca rompe la acción si falla el envío.
     */
    protected static function notifyCustomer(Reservation $reservation, string $subject, string $message): void
    {
        try {
            $settings = SiteSetting::current();
            $account = MailAccount::find($settings->notify_forms_account_id);

            if (! $account) {
                return;
            }

            $html = '<p>Hola ' . e($reservation->name) . ',</p>'
                . '<p>' . e($message) . '</p>'
                . ($reservation->service ? '<p><strong>Servicio:</strong> ' . e($reservation->service) . '</p>' : '')
                . '<p><strong>Fecha:</strong> ' . e($reservation->starts_at->format('d/m/Y H:i')) . '</p>'
                . '<p>' . e($settings->site_name) . '</p>';

            Mailer::send($account, $reservation->email, $subject . ' — ' . $settings->site_name, $html);
        } catch (\Throwable $e) {
            // Silencioso: el estado ya se actualizó.
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
