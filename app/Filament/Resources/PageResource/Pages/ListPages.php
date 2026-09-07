<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Page;
use App\Support\Pack;
use App\Support\PageTemplates;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('desdePlantilla')
                ->label('Crear desde plantilla')
                ->icon('heroicon-o-sparkles')
                ->color('gray')
                ->modalHeading('Crear página desde plantilla')
                ->modalSubmitActionLabel('Crear página')
                ->form([
                    Forms\Components\Select::make('template')
                        ->label('Plantilla')
                        ->options(PageTemplates::options())
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText(fn (Forms\Get $get) => optional(PageTemplates::get($get('template')))['description'] ?? 'Elige una composición lista para empezar.'),
                    Forms\Components\TextInput::make('title')
                        ->label('Título de la página')
                        ->required()
                        ->default('Nueva página'),
                ])
                ->action(function (array $data, \Livewire\Component $livewire) {
                    $tpl = PageTemplates::get($data['template']);

                    $base = Str::slug($data['title']) ?: 'pagina';
                    $slug = $base;
                    $i = 1;
                    while (Page::where('slug', $slug)->exists()) {
                        $slug = $base . '-' . $i++;
                    }

                    $page = Page::create([
                        'title' => $data['title'],
                        'slug' => $slug,
                        'content' => $tpl['blocks'] ?? [],
                        'is_published' => true,
                        'show_in_menu' => true,
                    ]);

                    Notification::make()
                        ->title('Página creada desde la plantilla "' . $tpl['name'] . '"')
                        ->body('Ya puedes editar su contenido.')
                        ->success()
                        ->send();

                    $livewire->redirect(PageResource::getUrl('edit', ['record' => $page->getKey()]));
                }),
            Actions\Action::make('importarPlantilla')
                ->label('Importar plantilla')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Importar plantilla desde un pack (.json)')
                ->modalSubmitActionLabel('Importar')
                ->form([
                    Forms\Components\FileUpload::make('archivo')
                        ->label('Archivo de la plantilla (.json)')
                        ->acceptedFileTypes(['application/json', 'text/plain'])
                        ->storeFiles(false)
                        ->required(),
                    Forms\Components\TextInput::make('title')
                        ->label('Título de la nueva página')
                        ->helperText('Opcional. Si lo dejas vacío, se usa el del pack.'),
                ])
                ->action(function (array $data, \Livewire\Component $livewire) {
                    $file = is_array($data['archivo']) ? reset($data['archivo']) : $data['archivo'];
                    $pack = Pack::parse($file?->get(), 'page-template');

                    if (! $pack) {
                        Notification::make()
                            ->title('Archivo no válido')
                            ->body('El archivo no es un pack de plantilla válido.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $page = Pack::importPage($pack, $data['title'] ?: null);

                    Notification::make()
                        ->title('Plantilla importada como "' . $page->title . '"')
                        ->success()
                        ->send();

                    $livewire->redirect(PageResource::getUrl('edit', ['record' => $page->getKey()]));
                }),
            Actions\CreateAction::make()->label('Nueva página'),
        ];
    }
}
