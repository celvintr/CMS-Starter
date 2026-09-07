<?php

namespace App\Filament\Resources\MailAccountResource\Pages;

use App\Filament\Resources\MailAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMailAccounts extends ListRecords
{
    protected static string $resource = MailAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nueva cuenta'),
        ];
    }
}
