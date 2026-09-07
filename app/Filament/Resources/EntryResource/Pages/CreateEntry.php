<?php

namespace App\Filament\Resources\EntryResource\Pages;

use App\Filament\Concerns\HasModuleContext;
use App\Filament\Resources\EntryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateEntry extends CreateRecord
{
    use HasModuleContext;

    protected static string $resource = EntryResource::class;

    public function getTitle(): string
    {
        return 'Nuevo ' . strtolower($this->currentModule()?->singularLabel() ?? 'registro');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['module_id'] = $this->currentModule()?->id;

        if (empty($data['slug']) && ! empty($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return EntryResource::getUrl('index') . '?module=' . $this->module;
    }
}
