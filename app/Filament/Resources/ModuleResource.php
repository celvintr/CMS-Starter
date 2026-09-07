<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModuleResource\Pages;
use App\Models\Module;
use App\Support\Pack;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $navigationLabel = 'Módulos';
    protected static ?string $modelLabel = 'módulo';
    protected static ?string $pluralModelLabel = 'módulos';
    protected static ?int $navigationSort = 90;

    /**
     * Definir módulos es tarea del administrador (no del cliente/editor).
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del módulo')
                ->description('Ej: Productos, Propiedades, Doctores, Cursos…')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('¿Qué tipo de módulo es?')
                        ->options(Module::types())
                        ->default('generico')
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText('Define el ícono, los campos sugeridos y cómo se ve en el sitio.')
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            $set('icon', Module::iconFor($state));
                            if (empty($get('fields'))) {
                                $set('fields', Module::fieldPresets($state));
                            }
                        })
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del módulo')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation !== 'create') {
                                return;
                            }
                            $set('slug', Str::slug($state));
                            $set('plural_label', $state);
                            $set('singular_label', Str::singular($state));
                        }),
                    Forms\Components\TextInput::make('slug')->label('Slug (URL)')->required()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('singular_label')->label('Etiqueta singular')->placeholder('Producto'),
                    Forms\Components\TextInput::make('plural_label')->label('Etiqueta plural')->placeholder('Productos'),
                    Forms\Components\Select::make('icon')
                        ->label('Ícono')
                        ->options([
                            'heroicon-o-rectangle-stack' => 'Pila',
                            'heroicon-o-shopping-bag' => 'Bolsa (productos)',
                            'heroicon-o-home-modern' => 'Casa (inmuebles)',
                            'heroicon-o-user-group' => 'Personas',
                            'heroicon-o-academic-cap' => 'Cursos',
                            'heroicon-o-briefcase' => 'Servicios',
                            'heroicon-o-photo' => 'Galería',
                            'heroicon-o-star' => 'Estrella',
                            'heroicon-o-tag' => 'Etiqueta',
                        ])
                        ->default('heroicon-o-rectangle-stack')
                        ->native(false),
                    Forms\Components\Toggle::make('is_public')
                        ->label('Mostrar en el sitio web')
                        ->helperText('Si se activa, tendrá listado y páginas públicas en el frontend.'),
                ])->columns(2),

            Forms\Components\Section::make('Campos')
                ->description('Los campos que tendrá cada registro de este módulo.')
                ->schema([
                    Forms\Components\Repeater::make('fields')
                        ->label('')
                        ->schema([
                            Forms\Components\TextInput::make('label')
                                ->label('Nombre del campo')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('key', Str::slug($state, '_'))),
                            Forms\Components\TextInput::make('key')
                                ->label('Clave interna')
                                ->required()
                                ->helperText('Sin espacios. Ej: precio, descripcion'),
                            Forms\Components\Select::make('type')
                                ->label('Tipo')
                                ->required()
                                ->default('text')
                                ->live()
                                ->native(false)
                                ->options([
                                    'text' => 'Texto corto',
                                    'textarea' => 'Texto largo',
                                    'richtext' => 'Editor con formato',
                                    'email' => 'Correo electrónico',
                                    'number' => 'Número / Precio',
                                    'boolean' => 'Sí / No',
                                    'date' => 'Fecha',
                                    'select' => 'Lista de opciones',
                                    'image' => 'Imagen',
                                    'gallery' => 'Galería de imágenes',
                                    'relation' => 'Relación (a otro módulo)',
                                ]),
                            Forms\Components\Toggle::make('required')->label('Obligatorio')->inline(false),
                            Forms\Components\TextInput::make('options')
                                ->label('Opciones (separadas por coma)')
                                ->helperText('Solo para "Lista de opciones". Ej: Rojo, Verde, Azul')
                                ->visible(fn (Forms\Get $get) => $get('type') === 'select')
                                ->columnSpanFull(),
                            Forms\Components\Select::make('relation_module')
                                ->label('¿A qué módulo se relaciona?')
                                ->options(fn () => Module::query()->pluck('name', 'slug')->all())
                                ->native(false)
                                ->visible(fn (Forms\Get $get) => $get('type') === 'relation')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->cloneable()
                        ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Nuevo campo')
                        ->addActionLabel('➕ Agregar campo')
                        ->defaultItems(0),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Módulo')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('entries_count')->counts('entries')->label('Registros'),
                Tables\Columns\IconColumn::make('is_public')->label('En el sitio')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\Action::make('exportar')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->form([
                        Forms\Components\Toggle::make('con_registros')
                            ->label('Incluir registros')
                            ->helperText('Incluye los registros cargados (útil para packs con contenido de ejemplo).')
                            ->default(false),
                    ])
                    ->action(function (Module $record, array $data) {
                        $pack = Pack::exportModule($record, (bool) ($data['con_registros'] ?? false));
                        $json = Pack::toJson($pack);

                        return response()->streamDownload(
                            fn () => print($json),
                            "modulo-{$record->slug}.json",
                            ['Content-Type' => 'application/json'],
                        );
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
            'create' => Pages\CreateModule::route('/create'),
            'edit' => Pages\EditModule::route('/{record}/edit'),
        ];
    }
}
