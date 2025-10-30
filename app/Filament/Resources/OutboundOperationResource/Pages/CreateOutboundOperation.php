<?php

namespace App\Filament\Resources\OutboundOperationResource\Pages;

use App\Filament\Resources\OutboundOperationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOutboundOperation extends CreateRecord
{
    protected static string $resource = OutboundOperationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
