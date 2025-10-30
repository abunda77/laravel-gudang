<?php

namespace App\Filament\Resources\OutboundOperationResource\Pages;

use App\Filament\Resources\OutboundOperationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOutboundOperations extends ListRecords
{
    protected static string $resource = OutboundOperationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
