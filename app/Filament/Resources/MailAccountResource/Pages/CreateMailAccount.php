<?php

namespace App\Filament\Resources\MailAccountResource\Pages;

use App\Filament\Resources\MailAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMailAccount extends CreateRecord
{
    protected static string $resource = MailAccountResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
