<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\MailAccount;
use App\Models\Subscriber;
use App\Support\Features;
use App\Support\Mailer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationGroup = 'Newsletter';
    protected static ?string $navigationLabel = 'Campañas';
    protected static ?string $modelLabel = 'campaña';
    protected static ?string $pluralModelLabel = 'campañas';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Features::enabled('newsletter');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('subject')
                        ->label('Asunto')
                        ->required()
                        ->disabled(fn (?Campaign $record) => $record?->isSent()),
                    Forms\Components\Select::make('mail_account_id')
                        ->label('Enviar desde')
                        ->options(fn () => MailAccount::pluck('name', 'id')->all())
                        ->native(false)
                        ->required()
                        ->helperText('Cuenta SMTP con la que se enviará. Créalas en Configuración → Cuentas de correo.')
                        ->disabled(fn (?Campaign $record) => $record?->isSent()),
                    Forms\Components\RichEditor::make('body')
                        ->label('Contenido')
                        ->disabled(fn (?Campaign $record) => $record?->isSent())
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('estado')
                        ->label('Estado')
                        ->content(fn (?Campaign $record) => $record?->isSent()
                            ? 'Enviada a ' . $record->recipients . ' suscriptor(es) el ' . optional($record->sent_at)->format('d/m/Y H:i')
                            : 'Borrador. Se enviará a todos los suscriptores activos.')
                        ->visibleOn('edit'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')->label('Asunto')->searchable()->weight('bold')->limit(50),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()
                    ->color(fn (string $state) => $state === 'sent' ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state) => $state === 'sent' ? 'Enviada' : 'Borrador'),
                Tables\Columns\TextColumn::make('recipients')->label('Enviados')->placeholder('—'),
                Tables\Columns\TextColumn::make('sent_at')->label('Fecha de envío')->dateTime('d/m/Y H:i')->placeholder('—')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('enviar')
                    ->label('Enviar')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Campaign $record) => ! $record->isSent())
                    ->requiresConfirmation()
                    ->modalHeading('Enviar campaña')
                    ->modalDescription('Se enviará a todos los suscriptores activos. Esta acción no se puede deshacer.')
                    ->action(fn (Campaign $record) => static::sendCampaign($record)),
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
     * Envía la campaña a todos los suscriptores activos, con enlace de baja
     * firmado por destinatario. Envío síncrono (listas por cliente, pequeñas).
     */
    public static function sendCampaign(Campaign $record): void
    {
        $account = $record->mailAccount;

        if (! $account) {
            Notification::make()->title('Elige una cuenta de correo para enviar.')->danger()->send();

            return;
        }

        $subscribers = Subscriber::active()->get();

        if ($subscribers->isEmpty()) {
            Notification::make()->title('No hay suscriptores activos.')->warning()->send();

            return;
        }

        $sent = 0;
        foreach ($subscribers as $subscriber) {
            $unsubscribe = URL::signedRoute('newsletter.unsubscribe', ['subscriber' => $subscriber->id]);
            $html = ($record->body ?: '')
                . '<hr style="margin-top:32px;border:none;border-top:1px solid #e2e8f0">'
                . '<p style="color:#94a3b8;font-size:12px;margin-top:12px">'
                . '¿No quieres recibir más correos? <a href="' . e($unsubscribe) . '" style="color:#94a3b8">Darte de baja</a>.'
                . '</p>';

            try {
                Mailer::send($account, $subscriber->email, $record->subject, $html);
                $sent++;
            } catch (\Throwable $e) {
                // Continuar con el resto aunque uno falle.
            }
        }

        $record->update(['status' => 'sent', 'recipients' => $sent, 'sent_at' => now()]);

        Notification::make()->title("Campaña enviada a {$sent} suscriptor(es).")->success()->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
