<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ModuleResource;
use App\Filament\Resources\PageResource;
use App\Support\AiGenerator;
use App\Support\Pack;
use App\Support\PackLibrary;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BibliotecaPacks extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $navigationLabel = 'Biblioteca de packs';
    protected static ?int $navigationSort = 89;
    protected static string $view = 'filament.pages.biblioteca-packs';

    public function getTitle(): string
    {
        return 'Biblioteca de packs';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generarIA')
                ->label('Generar con IA')
                ->icon('heroicon-o-sparkles')
                ->modalHeading('Generar con inteligencia artificial')
                ->modalDescription('Describe lo que necesitas y la IA lo crea, listo para editar.')
                ->modalSubmitActionLabel('Generar')
                ->form([
                    Forms\Components\Select::make('kind')
                        ->label('¿Qué quieres generar?')
                        ->options([
                            'module' => 'Un módulo (tipo de contenido)',
                            'page-template' => 'Una plantilla de página',
                        ])
                        ->default('module')
                        ->required()
                        ->native(false),
                    Forms\Components\Textarea::make('prompt')
                        ->label('Descríbelo')
                        ->rows(4)
                        ->required()
                        ->placeholder('Ej: un módulo de reservas de hotel con fecha de entrada, fecha de salida, tipo de habitación y número de huéspedes.'),
                ])
                ->action(function (array $data) {
                    try {
                        $pack = AiGenerator::generate($data['kind'], $data['prompt']);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('No se pudo generar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    if (($pack['type'] ?? null) === 'module') {
                        $module = Pack::importModule($pack);
                        Notification::make()->title('Módulo "' . $module->name . '" generado')->success()->send();
                        $this->redirect(ModuleResource::getUrl('edit', ['record' => $module->getKey()]));

                        return;
                    }

                    $page = Pack::importPage($pack);
                    Notification::make()->title('Plantilla "' . $page->title . '" generada')->success()->send();
                    $this->redirect(PageResource::getUrl('edit', ['record' => $page->getKey()]));
                }),
        ];
    }

    public function importar(string $key): void
    {
        $entry = PackLibrary::find($key);

        if (! $entry) {
            Notification::make()->title('Pack no encontrado')->danger()->send();

            return;
        }

        if ($entry['type'] === 'module') {
            $module = Pack::importModule($entry['pack']);
            Notification::make()->title('Módulo "' . $module->name . '" instalado')->success()->send();
            $this->redirect(ModuleResource::getUrl('edit', ['record' => $module->getKey()]));

            return;
        }

        $page = Pack::importPage($entry['pack']);
        Notification::make()->title('Plantilla "' . $page->title . '" instalada')->success()->send();
        $this->redirect(PageResource::getUrl('edit', ['record' => $page->getKey()]));
    }
}
