<?php

namespace App\Filament\Resources\InboundOperationResource\Pages;

use App\Filament\Resources\InboundOperationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInboundOperation extends CreateRecord
{
    protected static string $resource = InboundOperationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set received_by to current user
        $data['received_by'] = auth()->id();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
