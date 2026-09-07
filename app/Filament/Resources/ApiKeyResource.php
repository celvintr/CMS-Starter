<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiKeyResource\Pages;
use App\Models\ApiKey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'API keys';
    protected static ?string $modelLabel = 'API key';
    protected static ?string $pluralModelLabel = 'API keys';
    protected static ?int $navigationSort = 96;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre / para qué es')
                ->placeholder('Ej: App móvil, Sitio Next.js')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('last_used_at')->label('Último uso')->since()->placeholder('Nunca'),
                Tables\Columns\TextColumn::make('created_at')->label('Creada')->date('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\DeleteAction::make()->label('Revocar'),
            ])
            ->emptyStateHeading('Sin llaves todavía')
            ->emptyStateDescription('Crea una llave para conectar apps o sitios externos a tu contenido.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiKeys::route('/'),
            'create' => Pages\CreateApiKey::route('/create'),
        ];
    }
}
