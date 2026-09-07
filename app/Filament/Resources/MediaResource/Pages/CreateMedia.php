<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $disk = 'public';
        $path = $data['path'];

        $data['disk'] = $disk;

        try {
            $data['mime_type'] = Storage::disk($disk)->mimeType($path);
            $data['size'] = Storage::disk($disk)->size($path);
        } catch (\Throwable $e) {
            // si no se puede leer el archivo, se guarda sin metadatos
        }

        if (empty($data['name'])) {
            $data['name'] = basename($path);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
