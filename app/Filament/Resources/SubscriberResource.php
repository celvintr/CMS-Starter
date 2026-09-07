<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriberResource\Pages;
use App\Models\Subscriber;
use App\Support\Features;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriberResource extends Resource
{
    protected static ?string $model = Subscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Newsletter';
    protected static ?string $navigationLabel = 'Suscriptores';
    protected static ?string $modelLabel = 'suscriptor';
    protected static ?string $pluralModelLabel = 'suscriptores';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Features::enabled('newsletter');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('email')->label('Correo')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')->label('Nombre'),
            Forms\Components\Toggle::make('is_active')->label('Activo')->default(true)
                ->helperText('Apágalo para que no reciba campañas.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')->label('Correo')->searchable()->copyable()->weight('bold'),
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->placeholder('—'),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
                Tables\Columns\TextColumn::make('source')->label('Origen')->badge()->color('gray')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('Suscrito')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Activo'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportar')
                    ->label('Exportar CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $rows = Subscriber::orderBy('created_at')->get();
                        $csv = "email,nombre,activo,origen,fecha\n";
                        foreach ($rows as $r) {
                            $csv .= implode(',', [
                                '"' . str_replace('"', '""', $r->email) . '"',
                                '"' . str_replace('"', '""', (string) $r->name) . '"',
                                $r->is_active ? '1' : '0',
                                '"' . str_replace('"', '""', (string) $r->source) . '"',
                                optional($r->created_at)->format('Y-m-d H:i'),
                            ]) . "\n";
                        }

                        return response()->streamDownload(fn () => print($csv), 'suscriptores.csv', [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),
            ])
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
            'index' => Pages\ListSubscribers::route('/'),
            'create' => Pages\CreateSubscriber::route('/create'),
            'edit' => Pages\EditSubscriber::route('/{record}/edit'),
        ];
    }
}
