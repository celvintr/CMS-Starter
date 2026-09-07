<?php

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use App\Models\ApiKey;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateApiKey extends CreateRecord
{
    protected static string $resource = ApiKeyResource::class;

    protected ?string $plainToken = null;

    protected function handleRecordCreation(array $data): Model
    {
        [$plain, $key] = ApiKey::generate($data['name']);
        $this->plainToken = $plain;

        return $key;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('API key creada — cópiala ahora')
            ->body('No se volverá a mostrar:  ' . $this->plainToken)
            ->success()
            ->persistent();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
