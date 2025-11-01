<?php

namespace App\Filament\Resources\InboundOperationResource\Pages;

use App\Filament\Resources\InboundOperationResource;
use App\Services\StockMovementService;
use Filament\Notifications\Notification;
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

    protected function afterCreate(): void
    {
        // Record stock movements for received items
        $stockService = app(StockMovementService::class);
        
        try {
            $items = $this->record->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'received_quantity' => $item->received_quantity,
                ];
            })->toArray();
            
            $stockService->recordInbound($this->record, $items);
            
            Notification::make()
                ->success()
                ->title('Stock Updated')
                ->body('Stock movements have been recorded successfully.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Stock Update Failed')
                ->body('Failed to record stock movements: ' . $e->getMessage())
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
