<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Medios';
    protected static ?string $modelLabel = 'medio';
    protected static ?string $pluralModelLabel = 'biblioteca de medios';
    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('path')
                ->label('Imagen')
                ->image()
                ->maxSize(5120)
                ->disk('public')
                ->directory('biblioteca')
                ->required(),
            Forms\Components\TextInput::make('name')
                ->label('Nombre (opcional)')
                ->helperText('Para identificarla en la biblioteca.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')->label('')->disk('public')->height(48),
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('url')->label('URL')
                    ->copyable()->copyMessage('URL copiada')->limit(34)->color('gray'),
                Tables\Columns\TextColumn::make('size_for_humans')->label('Tamaño'),
                Tables\Columns\TextColumn::make('created_at')->label('Subida')->date('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->before(fn (Media $record) => Storage::disk($record->disk)->delete($record->path)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(fn ($records) => $records->each(fn (Media $r) => Storage::disk($r->disk)->delete($r->path))),
                ]),
            ])
            ->emptyStateHeading('Biblioteca vacía')
            ->emptyStateDescription('Sube imágenes para reutilizarlas en tu contenido copiando su URL.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
        ];
    }
}
