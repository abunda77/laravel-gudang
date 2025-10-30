<?php

namespace App\Filament\Resources\OutboundOperationResource\Pages;

use App\Filament\Resources\OutboundOperationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutboundOperation extends EditRecord
{
    protected static string $resource = OutboundOperationResource::class;

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
