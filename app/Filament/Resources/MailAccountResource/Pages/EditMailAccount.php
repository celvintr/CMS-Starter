<?php

namespace App\Filament\Resources\MailAccountResource\Pages;

use App\Filament\Resources\MailAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMailAccount extends EditRecord
{
    protected static string $resource = MailAccountResource::class;

    /**
     * No exponer la contraseña existente en el formulario.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['password'] = '';

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
