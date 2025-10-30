<?php

namespace App\Filament\Resources\InboundOperationResource\Pages;

use App\Filament\Resources\InboundOperationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInboundOperations extends ListRecords
{
    protected static string $resource = InboundOperationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
