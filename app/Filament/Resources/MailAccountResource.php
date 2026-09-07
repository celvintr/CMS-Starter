<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailAccountResource\Pages;
use App\Models\MailAccount;
use App\Support\Mailer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MailAccountResource extends Resource
{
    protected static ?string $model = MailAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Cuentas de correo';
    protected static ?string $modelLabel = 'cuenta de correo';
    protected static ?string $pluralModelLabel = 'cuentas de correo';
    protected static ?int $navigationSort = 97;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->label('Nombre')->required()->placeholder('Ej: Ventas, Soporte'),
                Forms\Components\Select::make('encryption')->label('Cifrado')
                    ->options(['tls' => 'TLS', 'ssl' => 'SSL', '' => 'Ninguno'])->default('tls')->native(false),
                Forms\Components\TextInput::make('host')->label('Host SMTP')->required()->placeholder('smtp.gmail.com'),
                Forms\Components\TextInput::make('port')->label('Puerto')->numeric()->default(587)->required(),
                Forms\Components\TextInput::make('username')->label('Usuario'),
                Forms\Components\TextInput::make('password')->label('Contraseña')
                    ->password()->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText('Déjala vacía al editar para no cambiarla.'),
                Forms\Components\TextInput::make('from_address')->label('Remitente (correo)')->email()
                    ->placeholder('no-reply@tudominio.com'),
                Forms\Components\TextInput::make('from_name')->label('Nombre del remitente'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('host')->label('Host'),
                Tables\Columns\TextColumn::make('from_address')->label('Remitente'),
                Tables\Columns\TextColumn::make('created_at')->label('Creada')->date('d/m/Y')->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('probar')
                    ->label('Enviar prueba')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('gray')
                    ->form([
                        Forms\Components\TextInput::make('to')->label('Enviar prueba a')->email()->required(),
                    ])
                    ->action(function (MailAccount $record, array $data) {
                        try {
                            Mailer::send($record, $data['to'], 'Correo de prueba — ' . $record->name,
                                '<p>¡Funciona! Esta es una prueba de la cuenta <strong>' . e($record->name) . '</strong>.</p>');
                            Notification::make()->title('Correo de prueba enviado')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('No se pudo enviar')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMailAccounts::route('/'),
            'create' => Pages\CreateMailAccount::route('/create'),
            'edit' => Pages\EditMailAccount::route('/{record}/edit'),
        ];
    }
}
