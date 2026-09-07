<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ModuleResource;
use App\Filament\Resources\PageResource;
use App\Support\Pack;
use App\Support\PackLibrary;
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
