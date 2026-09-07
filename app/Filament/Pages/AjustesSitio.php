<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AjustesSitio extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Ajustes del sitio';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int $navigationSort = 100;
    protected static string $view = 'filament.pages.ajustes-sitio';

    public ?array $data = [];

    /**
     * Solo el administrador puede cambiar los ajustes globales del sitio.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getTitle(): string
    {
        return 'Ajustes del sitio';
    }

    public function mount(): void
    {
        $attrs = SiteSetting::current()->attributesToArray();
        // No exponer secretos en el formulario (se guardan encriptados).
        $attrs['ai_api_key'] = '';
        $attrs['stripe_secret_key'] = '';
        $attrs['stripe_webhook_secret'] = '';
        $this->form->fill($attrs);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identidad')
                    ->description('Nombre, logo y descripción de tu sitio.')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')->label('Nombre del sitio')->required(),
                        Forms\Components\TextInput::make('tagline')->label('Eslogan / Frase'),
                        Forms\Components\FileUpload::make('logo_path')->label('Logo')
                            ->image()->maxSize(3072)->disk('public')->directory('sitio'),
                    ])->columns(2),

                Forms\Components\Section::make('Colores')
                    ->description('Definen el color de botones y encabezados del sitio.')
                    ->schema([
                        Forms\Components\ColorPicker::make('primary_color')->label('Color principal'),
                        Forms\Components\ColorPicker::make('secondary_color')->label('Color secundario'),
                    ])->columns(2),

                Forms\Components\Section::make('Contacto')
                    ->schema([
                        Forms\Components\TextInput::make('whatsapp')->label('WhatsApp')
                            ->helperText('Con código de país. Ej: +504 9999-9999'),
                        Forms\Components\TextInput::make('phone')->label('Teléfono'),
                        Forms\Components\TextInput::make('email')->label('Correo')->email(),
                        Forms\Components\TextInput::make('address')->label('Dirección'),
                    ])->columns(2),

                Forms\Components\Section::make('Redes sociales')
                    ->schema([
                        Forms\Components\TextInput::make('facebook')->label('Facebook')->url()->prefixIcon('heroicon-o-globe-alt'),
                        Forms\Components\TextInput::make('instagram')->label('Instagram')->url()->prefixIcon('heroicon-o-globe-alt'),
                        Forms\Components\TextInput::make('tiktok')->label('TikTok')->url()->prefixIcon('heroicon-o-globe-alt'),
                    ])->columns(3)->collapsed(),

                Forms\Components\Section::make('SEO y pie de página')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')->label('Título SEO por defecto'),
                        Forms\Components\Textarea::make('meta_description')->label('Descripción SEO por defecto')->rows(2),
                        Forms\Components\Textarea::make('footer_text')->label('Texto del pie de página')->rows(2),
                    ])->collapsed(),

                Forms\Components\Section::make('Inteligencia artificial')
                    ->description('Conecta tu propia API para generar módulos y plantillas con IA.')
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        Forms\Components\Select::make('ai_provider')->label('Proveedor')
                            ->options([
                                'openrouter' => 'OpenRouter (recomendado: da acceso a OpenAI, Gemini, Claude…)',
                                'openai' => 'OpenAI',
                            ])
                            ->default('openrouter')->native(false),
                        Forms\Components\TextInput::make('ai_api_key')->label('API key')
                            ->password()->revealable()
                            ->placeholder('•••••••• (se guarda encriptada)')
                            ->helperText('Déjala vacía para no cambiar la que ya tienes.'),
                        Forms\Components\TextInput::make('ai_model')->label('Modelo')
                            ->placeholder('openai/gpt-4o-mini')
                            ->helperText('Opcional. En OpenRouter, ej: openai/gpt-4o-mini, google/gemini-flash-1.5, anthropic/claude-3.5-sonnet.'),
                    ])->columns(2)->collapsed(),

                Forms\Components\Section::make('Pagos (Stripe)')
                    ->description('Cobra con tarjeta en la tienda mediante Stripe Checkout (seguro).')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Forms\Components\Toggle::make('stripe_enabled')
                            ->label('Activar pagos con tarjeta')
                            ->helperText('El botón de pago aparece en el carrito solo si está activo y configurado.'),
                        Forms\Components\TextInput::make('currency')
                            ->label('Moneda')
                            ->placeholder('usd')
                            ->helperText('Código ISO de 3 letras, ej: usd, mxn, eur.')
                            ->maxLength(3),
                        Forms\Components\TextInput::make('stripe_public_key')
                            ->label('Clave publicable (pk_...)'),
                        Forms\Components\TextInput::make('stripe_secret_key')
                            ->label('Clave secreta (sk_...)')
                            ->password()->revealable()
                            ->placeholder('•••••••• (se guarda encriptada)')
                            ->helperText('Déjala vacía para no cambiar la existente.'),
                        Forms\Components\TextInput::make('stripe_webhook_secret')
                            ->label('Secreto del webhook (whsec_...)')
                            ->password()->revealable()
                            ->placeholder('•••••••• (se guarda encriptada)')
                            ->helperText('Del endpoint /stripe/webhook en tu panel de Stripe.'),
                    ])->columns(2)->collapsed(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Si no escribieron un secreto nuevo, conservar el existente.
        foreach (['ai_api_key', 'stripe_secret_key', 'stripe_webhook_secret'] as $secret) {
            if (empty($data[$secret])) {
                unset($data[$secret]);
            }
        }

        SiteSetting::current()->update($data);

        Notification::make()
            ->title('Ajustes guardados correctamente')
            ->success()
            ->send();
    }
}
