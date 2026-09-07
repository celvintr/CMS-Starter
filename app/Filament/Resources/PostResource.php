<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Blog / Noticias';
    protected static ?string $modelLabel = 'entrada';
    protected static ?string $pluralModelLabel = 'entradas del blog';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation === 'create') {
                                $set('slug', Str::slug($state));
                            }
                        })
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('excerpt')
                        ->label('Resumen / Extracto')
                        ->rows(2)
                        ->helperText('Se muestra en el listado del blog.')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('body')
                        ->label('Contenido')
                        ->columnSpanFull(),
                ])->columnSpan(2),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Publicación')->schema([
                        Forms\Components\Toggle::make('is_published')->label('Publicada')->default(true),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Fecha de publicación')
                            ->default(now()),
                        Forms\Components\TextInput::make('slug')
                            ->label('URL (slug)')->required()->unique(ignoreRecord: true),
                        Forms\Components\FileUpload::make('cover_image')
                            ->label('Imagen de portada')
                            ->image()->maxSize(5120)->disk('public')->directory('blog'),
                    ]),
                    Forms\Components\Section::make('SEO')->schema([
                        Forms\Components\TextInput::make('meta_title')->label('Título SEO')->maxLength(70),
                        Forms\Components\Textarea::make('meta_description')->label('Descripción SEO')->rows(3)->maxLength(180),
                    ])->collapsed(),
                ])->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')->label('')->disk('public')->height(40),
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_published')->label('Publicada')->boolean(),
                Tables\Columns\TextColumn::make('published_at')->label('Fecha')->date('d/m/Y')->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
