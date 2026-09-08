<?php

namespace App\Filament\Pages;

use App\Models\MailAccount;
use App\Models\Module;
use App\Models\Page as PageModel;
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
        $attrs['paypal_secret'] = '';
        $this->form->fill($attrs);
    }

    public function form(Form $form): Form
    {
        // Campos de traducción generados a partir de los idiomas ya guardados.
        $settings = SiteSetting::current();
        $default = $settings->defaultLanguage();
        $langOptions = collect($settings->activeLanguages())->pluck('name', 'code')->all();

        $translationFields = [];
        foreach ($settings->activeLanguages() as $lang) {
            if (($lang['code'] ?? null) === $default) {
                continue;
            }
            $code = $lang['code'];
            $translationFields[] = Forms\Components\Fieldset::make('Traducción · ' . ($lang['name'] ?? strtoupper($code)))
                ->schema([
                    Forms\Components\TextInput::make("translations.{$code}.site_name")->label('Nombre del sitio'),
                    Forms\Components\TextInput::make("translations.{$code}.tagline")->label('Eslogan / Frase'),
                    Forms\Components\TextInput::make("translations.{$code}.meta_title")->label('Título SEO'),
                    Forms\Components\Textarea::make("translations.{$code}.meta_description")->label('Descripción SEO')->rows(2),
                    Forms\Components\Textarea::make("translations.{$code}.footer_text")->label('Texto del pie de página')->rows(2),
                ])->columns(2);
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Identidad')
                    ->description('Nombre, logo y descripción de tu sitio.')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')->label('Nombre del sitio')->required(),
                        Forms\Components\TextInput::make('tagline')->label('Eslogan / Frase'),
                        Forms\Components\FileUpload::make('logo_path')->label('Logo')
                            ->image()->maxSize(3072)->disk('public')->directory('sitio'),
                        Forms\Components\FileUpload::make('favicon_path')->label('Favicon')
                            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'])
                            ->maxSize(512)->disk('public')->directory('sitio')
                            ->helperText('Ícono de la pestaña del navegador. Cuadrado, ideal 32×32 o 64×64 (PNG, SVG o ICO).'),
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

                Forms\Components\Section::make('Menú de navegación')
                    ->description('Arma tu menú. Si lo dejas vacío, se usa el automático (páginas + módulos + blog).')
                    ->icon('heroicon-o-bars-3')
                    ->schema([
                        Forms\Components\Repeater::make('menu')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('label')->label('Texto')->required(),
                                Forms\Components\Select::make('type')->label('Enlaza a')
                                    ->options([
                                        'page' => 'Página',
                                        'module' => 'Módulo',
                                        'blog' => 'Blog',
                                        'home' => 'Inicio',
                                        'custom' => 'Enlace personalizado',
                                    ])->default('page')->required()->live()->native(false),
                                Forms\Components\Select::make('page')->label('Página')
                                    ->options(fn () => PageModel::pluck('title', 'slug')->all())
                                    ->native(false)
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'page'),
                                Forms\Components\Select::make('module')->label('Módulo')
                                    ->options(fn () => Module::where('is_public', true)->pluck('name', 'slug')->all())
                                    ->native(false)
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'module'),
                                Forms\Components\TextInput::make('url')->label('URL')
                                    ->placeholder('https://…')
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'custom'),
                                Forms\Components\Toggle::make('new_tab')->label('Abrir en pestaña nueva')->inline(false),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Enlace')
                            ->addActionLabel('Agregar enlace')
                            ->defaultItems(0),
                    ])->collapsed(),

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

                Forms\Components\Section::make('Analítica y cookies')
                    ->description('Scripts de seguimiento y aviso de cookies del sitio.')
                    ->icon('heroicon-o-chart-pie')
                    ->schema([
                        Forms\Components\Textarea::make('analytics_head')
                            ->label('Código en el <head>')->rows(4)
                            ->helperText('Pega aquí el snippet de Google Analytics 4, Meta Pixel, TikTok Pixel, etc.'),
                        Forms\Components\Textarea::make('analytics_body')
                            ->label('Código antes de </body>')->rows(3)
                            ->helperText('Para chats en vivo, píxeles noscript u otros scripts que van al final.'),
                        Forms\Components\Toggle::make('cookie_banner')
                            ->label('Mostrar banner de cookies')->live()
                            ->helperText('Si lo activas, los scripts de arriba solo se cargan cuando el visitante acepta.'),
                        Forms\Components\TextInput::make('cookie_text')
                            ->label('Texto del banner')
                            ->placeholder('Usamos cookies para mejorar tu experiencia.')
                            ->visible(fn (Forms\Get $get) => $get('cookie_banner')),
                        Forms\Components\TextInput::make('cookie_policy_url')
                            ->label('Enlace a la política (opcional)')->url()
                            ->placeholder('https://…')
                            ->visible(fn (Forms\Get $get) => $get('cookie_banner')),
                    ])->collapsed(),

                Forms\Components\Section::make('Idiomas y traducciones')
                    ->description('Sitio multilenguaje con URLs por idioma (/en, /es…). El idioma por defecto usa los campos de arriba; los demás se traducen aquí y en cada página o entrada del blog.')
                    ->icon('heroicon-o-language')
                    ->visible(fn () => \App\Support\Features::enabled('multilenguaje'))
                    ->schema(array_merge([
                        Forms\Components\Select::make('default_language')
                            ->label('Idioma por defecto')
                            ->options($langOptions + ['es' => 'Español', 'en' => 'English'])
                            ->default('es')->native(false)->required()
                            ->helperText('Se sirve en la raíz del sitio, sin prefijo en la URL.'),
                        Forms\Components\Repeater::make('languages')
                            ->label('Idiomas disponibles')
                            ->schema([
                                Forms\Components\TextInput::make('code')->label('Código')->placeholder('en')
                                    ->required()->maxLength(5)
                                    ->helperText('ISO: en, fr, pt, de…'),
                                Forms\Components\TextInput::make('name')->label('Nombre')->placeholder('English')->required(),
                            ])
                            ->columns(2)->reorderable()->addActionLabel('Agregar idioma')->defaultItems(0)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? $state['code'] ?? 'Idioma')
                            ->helperText('Incluye también tu idioma por defecto. Guarda para habilitar sus campos de traducción abajo.'),
                    ], $translationFields))
                    ->collapsed(),

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

                Forms\Components\Section::make('Pagos (PayPal)')
                    ->description('Cobra con PayPal (el cliente aprueba y paga en PayPal).')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Forms\Components\Toggle::make('paypal_enabled')
                            ->label('Activar pagos con PayPal'),
                        Forms\Components\Select::make('paypal_mode')
                            ->label('Modo')
                            ->options(['sandbox' => 'Sandbox (pruebas)', 'live' => 'Producción'])
                            ->default('sandbox')->native(false),
                        Forms\Components\TextInput::make('paypal_client_id')
                            ->label('Client ID'),
                        Forms\Components\TextInput::make('paypal_secret')
                            ->label('Secret')
                            ->password()->revealable()
                            ->placeholder('•••••••• (se guarda encriptada)')
                            ->helperText('Déjalo vacío para no cambiar el existente.'),
                    ])->columns(2)->collapsed(),

                Forms\Components\Section::make('Envíos')
                    ->description('Costo de envío para las órdenes de la tienda.')
                    ->icon('heroicon-o-truck')
                    ->visible(fn () => \App\Support\Features::enabled('tienda'))
                    ->schema([
                        Forms\Components\Toggle::make('shipping_enabled')->label('Cobrar envío')->live(),
                        Forms\Components\TextInput::make('shipping_cost')->label('Costo de envío')
                            ->numeric()->minValue(0)
                            ->visible(fn (Forms\Get $get) => $get('shipping_enabled')),
                        Forms\Components\TextInput::make('shipping_free_from')->label('Envío gratis desde (subtotal)')
                            ->numeric()->minValue(0)
                            ->helperText('Opcional. Si el subtotal alcanza este monto, el envío es gratis.')
                            ->visible(fn (Forms\Get $get) => $get('shipping_enabled')),
                    ])->columns(2)->collapsed(),

                Forms\Components\Section::make('Horarios de reservas')
                    ->description('Días y horas en que se pueden agendar citas.')
                    ->icon('heroicon-o-clock')
                    ->visible(fn () => \App\Support\Features::enabled('reservas'))
                    ->schema([
                        Forms\Components\CheckboxList::make('reservation_days')
                            ->label('Días disponibles')
                            ->options([1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'])
                            ->columns(4)
                            ->helperText('Vacío = Lunes a Viernes.')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('reservation_open')->label('Apertura')
                            ->placeholder('09:00')->rule('regex:/^\d{2}:\d{2}$/'),
                        Forms\Components\TextInput::make('reservation_close')->label('Cierre')
                            ->placeholder('17:00')->rule('regex:/^\d{2}:\d{2}$/'),
                        Forms\Components\TextInput::make('reservation_slot_minutes')->label('Duración del turno (min)')
                            ->numeric()->default(30)->minValue(5),
                        Forms\Components\TextInput::make('reservation_capacity')->label('Cupos por horario')
                            ->numeric()->default(1)->minValue(1),
                    ])->columns(2)->collapsed(),

                Forms\Components\Section::make('Notificaciones por correo')
                    ->description('Elige por cuál cuenta y a quién avisar cuando llega un mensaje o una orden.')
                    ->icon('heroicon-o-bell-alert')
                    ->schema([
                        Forms\Components\Placeholder::make('nota_smtp')
                            ->label('')
                            ->content('Primero crea tus cuentas SMTP en Configuración → Cuentas de correo.')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('notify_forms_account_id')
                            ->label('Formularios y mensajes: cuenta')
                            ->options(fn () => MailAccount::pluck('name', 'id')->all())
                            ->native(false)->placeholder('Sin notificar'),
                        Forms\Components\TextInput::make('notify_forms_email')
                            ->label('Formularios y mensajes: enviar a')->email(),
                        Forms\Components\Select::make('notify_orders_account_id')
                            ->label('Órdenes (Stripe/PayPal): cuenta')
                            ->options(fn () => MailAccount::pluck('name', 'id')->all())
                            ->native(false)->placeholder('Sin notificar'),
                        Forms\Components\TextInput::make('notify_orders_email')
                            ->label('Órdenes: enviar a')->email(),
                    ])->columns(2)->collapsed(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Si no escribieron un secreto nuevo, conservar el existente.
        foreach (['ai_api_key', 'stripe_secret_key', 'stripe_webhook_secret', 'paypal_secret'] as $secret) {
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
