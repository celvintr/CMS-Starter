<?php

namespace App\Filament\Resources\EntryResource\Pages;

use App\Filament\Concerns\HasModuleContext;
use App\Filament\Resources\EntryResource;
use App\Models\Module;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEntry extends EditRecord
{
    use HasModuleContext;

    protected static string $resource = EntryResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->module = $this->record->module?->slug;
    }

    public function currentModule(): ?Module
    {
        return $this->record?->module ?? ($this->module ? Module::firstWhere('slug', $this->module) : null);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return EntryResource::getUrl('index') . '?module=' . $this->module;
    }
}
