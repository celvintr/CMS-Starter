<?php

namespace App\Filament\Resources\ModuleResource\Pages;

use App\Filament\Resources\ModuleResource;
use App\Support\Pack;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListModules extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importar')
                ->label('Importar módulo')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Importar módulo desde un pack (.json)')
                ->modalSubmitActionLabel('Importar')
                ->form([
                    Forms\Components\FileUpload::make('archivo')
                        ->label('Archivo del módulo (.json)')
                        ->acceptedFileTypes(['application/json', 'text/plain'])
                        ->storeFiles(false)
                        ->required(),
                ])
                ->action(function (array $data, \Livewire\Component $livewire) {
                    $file = is_array($data['archivo']) ? reset($data['archivo']) : $data['archivo'];
                    $pack = Pack::parse($file?->get(), 'module');

                    if (! $pack) {
                        Notification::make()
                            ->title('Archivo no válido')
                            ->body('El archivo no es un pack de módulo válido.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $module = Pack::importModule($pack);

                    Notification::make()
                        ->title('Módulo "' . $module->name . '" importado')
                        ->success()
                        ->send();

                    $livewire->redirect(ModuleResource::getUrl('edit', ['record' => $module->getKey()]));
                }),
            Actions\CreateAction::make()->label('Nuevo módulo'),
        ];
    }
}
