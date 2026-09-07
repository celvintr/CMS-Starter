<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCampaign extends EditRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('enviar')
                ->label('Enviar')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn () => ! $this->record->isSent())
                ->requiresConfirmation()
                ->modalHeading('Enviar campaña')
                ->modalDescription('Se enviará a todos los suscriptores activos. Esta acción no se puede deshacer.')
                ->action(function () {
                    CampaignResource::sendCampaign($this->record);
                    $this->refreshFormData(['status', 'recipients', 'sent_at']);
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
