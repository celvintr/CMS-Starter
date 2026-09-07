<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Module;
use App\Models\Page;
use App\Support\Pack;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Páginas';
    protected static ?string $modelLabel = 'página';
    protected static ?string $pluralModelLabel = 'páginas';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                // Columna principal: constructor de bloques
                Forms\Components\Group::make()->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Título de la página')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation === 'create') {
                                $set('slug', Str::slug($state));
                            }
                        })
                        ->columnSpanFull(),

                    Forms\Components\Builder::make('content')
                        ->label('Contenido de la página')
                        ->blocks(static::contentBlocks())
                        ->collapsible()
                        ->cloneable()
                        ->blockNumbers(false)
                        ->addActionLabel('➕ Agregar bloque')
                        ->columnSpanFull(),
                ])->columnSpan(2),

                // Barra lateral: ajustes de la página
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Publicación')->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('Publicada')
                            ->default(true),
                        Forms\Components\Toggle::make('show_in_menu')
                            ->label('Mostrar en el menú')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden en el menú')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('slug')
                            ->label('URL (slug)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Ej: nosotros, servicios, contacto'),
                    ]),
                    Forms\Components\Section::make('SEO')->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Título SEO')
                            ->maxLength(70),
                        Forms\Components\Textarea::make('meta_description')
                            ->label('Descripción SEO')
                            ->rows(3)
                            ->maxLength(180),
                    ])->collapsed(),
                ])->columnSpan(1),
            ]),
        ]);
    }

    /**
     * Los bloques disponibles en el constructor de páginas.
     */
    public static function contentBlocks(): array
    {
        return [
            Forms\Components\Builder\Block::make('hero')
                ->label('Portada / Hero')
                ->icon('heroicon-o-photo')
                ->schema([
                    Forms\Components\TextInput::make('heading')->label('Título grande')->required(),
                    Forms\Components\Textarea::make('subheading')->label('Subtítulo')->rows(2),
                    Forms\Components\FileUpload::make('image')->label('Imagen de fondo')
                        ->image()->disk('public')->directory('hero'),
                    Forms\Components\TextInput::make('button_text')->label('Texto del botón'),
                    Forms\Components\TextInput::make('button_url')->label('Enlace del botón')->default('#contacto'),
                ]),

            Forms\Components\Builder\Block::make('richtext')
                ->label('Texto con formato')
                ->icon('heroicon-o-bars-3-bottom-left')
                ->schema([
                    Forms\Components\RichEditor::make('content')->label('Contenido')->required(),
                ]),

            Forms\Components\Builder\Block::make('features')
                ->label('Servicios / Características')
                ->icon('heroicon-o-squares-2x2')
                ->schema([
                    Forms\Components\TextInput::make('heading')->label('Título de la sección'),
                    Forms\Components\Repeater::make('items')->label('Elementos')->schema([
                        Forms\Components\Select::make('icon')->label('Ícono')
                            ->native(false)
                            ->default('bolt')
                            ->options([
                                'bolt' => 'Rayo (rapidez)',
                                'shield' => 'Escudo (confianza)',
                                'sparkles' => 'Destello (innovación)',
                                'check' => 'Check (calidad)',
                                'truck' => 'Camión (envíos)',
                                'clock' => 'Reloj (tiempo)',
                                'heart' => 'Corazón (cercanía)',
                                'star' => 'Estrella (calidad)',
                                'chat' => 'Chat (soporte)',
                                'phone' => 'Teléfono (atención)',
                                'tag' => 'Etiqueta (precio)',
                                'cube' => 'Caja (producto)',
                            ]),
                        Forms\Components\TextInput::make('title')->label('Título')->required(),
                        Forms\Components\Textarea::make('text')->label('Descripción')->rows(2),
                    ])->columns(3)->defaultItems(3)->grid(3)->addActionLabel('Agregar elemento'),
                ]),

            Forms\Components\Builder\Block::make('gallery')
                ->label('Galería de imágenes')
                ->icon('heroicon-o-rectangle-stack')
                ->schema([
                    Forms\Components\TextInput::make('heading')->label('Título de la galería'),
                    Forms\Components\FileUpload::make('images')->label('Imágenes')
                        ->image()->multiple()->reorderable()
                        ->disk('public')->directory('galeria'),
                ]),

            Forms\Components\Builder\Block::make('image_text')
                ->label('Imagen + Texto')
                ->icon('heroicon-o-view-columns')
                ->schema([
                    Forms\Components\FileUpload::make('image')->label('Imagen')
                        ->image()->disk('public')->directory('secciones'),
                    Forms\Components\Select::make('image_side')->label('Lado de la imagen')
                        ->options(['left' => 'Izquierda', 'right' => 'Derecha'])->default('left'),
                    Forms\Components\TextInput::make('heading')->label('Título'),
                    Forms\Components\RichEditor::make('text')->label('Texto'),
                ]),

            Forms\Components\Builder\Block::make('cta')
                ->label('Llamada a la acción (CTA)')
                ->icon('heroicon-o-megaphone')
                ->schema([
                    Forms\Components\TextInput::make('heading')->label('Título')->required(),
                    Forms\Components\Textarea::make('text')->label('Texto')->rows(2),
                    Forms\Components\TextInput::make('button_text')->label('Texto del botón')->default('Contáctanos'),
                    Forms\Components\TextInput::make('button_url')->label('Enlace del botón')->default('#contacto'),
                ]),

            Forms\Components\Builder\Block::make('contact')
                ->label('Formulario de contacto')
                ->icon('heroicon-o-envelope')
                ->schema([
                    Forms\Components\TextInput::make('heading')->label('Título')->default('Contáctanos'),
                    Forms\Components\Textarea::make('subheading')->label('Subtítulo')->rows(2),
                ]),

            Forms\Components\Builder\Block::make('module_list')
                ->label('Listado de módulo')
                ->icon('heroicon-o-squares-plus')
                ->schema([
                    Forms\Components\Select::make('module')
                        ->label('¿Qué módulo mostrar?')
                        ->options(fn () => Schema::hasTable('modules') ? Module::pluck('name', 'slug')->all() : [])
                        ->required()
                        ->native(false)
                        ->helperText('Ej: Productos, Servicios, Equipo…'),
                    Forms\Components\TextInput::make('heading')->label('Título de la sección'),
                    Forms\Components\TextInput::make('limit')->label('¿Cuántos mostrar?')->numeric()->default(6),
                    Forms\Components\Select::make('columns')->label('Columnas')
                        ->options([2 => '2', 3 => '3', 4 => '4'])->default(3)->native(false),
                    Forms\Components\Toggle::make('show_link')->label('Mostrar botón "Ver todos"')->default(true),
                ]),

            Forms\Components\Builder\Block::make('form')
                ->label('Formulario')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Forms\Components\Select::make('module')
                        ->label('¿Qué formulario mostrar?')
                        ->options(fn () => Schema::hasTable('modules')
                            ? Module::where('type', 'formulario')->pluck('name', 'slug')->all()
                            : [])
                        ->required()
                        ->native(false)
                        ->helperText('Debe ser un módulo de tipo "Formulario".'),
                    Forms\Components\TextInput::make('heading')->label('Título')->default('Escríbenos'),
                    Forms\Components\Textarea::make('subheading')->label('Subtítulo')->rows(2),
                    Forms\Components\TextInput::make('button_text')->label('Texto del botón')->default('Enviar'),
                ]),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('URL')->badge()->color('gray'),
                Tables\Columns\IconColumn::make('is_published')->label('Publicada')->boolean(),
                Tables\Columns\IconColumn::make('show_in_menu')->label('En menú')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizada')->since()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\Action::make('exportar')
                    ->label('Exportar plantilla')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function (Page $record) {
                        $json = Pack::toJson(Pack::exportPage($record));

                        return response()->streamDownload(
                            fn () => print($json),
                            "plantilla-{$record->slug}.json",
                            ['Content-Type' => 'application/json'],
                        );
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
