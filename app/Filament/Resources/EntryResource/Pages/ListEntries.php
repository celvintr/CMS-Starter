<?php

namespace App\Filament\Resources\EntryResource\Pages;

use App\Filament\Concerns\HasModuleContext;
use App\Filament\Resources\EntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEntries extends ListRecords
{
    use HasModuleContext;

    protected static string $resource = EntryResource::class;

    public function getTitle(): string
    {
        return $this->currentModule()?->pluralLabel() ?? 'Registros';
    }

    protected function getHeaderActions(): array
    {
        $module = $this->currentModule();

        return [
            Actions\CreateAction::make()
                ->label('Nuevo ' . strtolower($module?->singularLabel() ?? 'registro'))
                ->url(EntryResource::getUrl('create') . '?module=' . $this->module),
        ];
    }
}
