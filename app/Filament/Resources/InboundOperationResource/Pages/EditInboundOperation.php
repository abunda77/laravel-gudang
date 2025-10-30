<?php

namespace App\Filament\Resources\InboundOperationResource\Pages;

use App\Filament\Resources\InboundOperationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInboundOperation extends EditRecord
{
    protected static string $resource = InboundOperationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => !$this->record->stockMovements()->exists()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
