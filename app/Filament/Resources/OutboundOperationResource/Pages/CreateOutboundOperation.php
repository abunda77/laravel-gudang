<?php

namespace App\Filament\Resources\OutboundOperationResource\Pages;

use App\Filament\Resources\OutboundOperationResource;
use App\Services\StockMovementService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateOutboundOperation extends CreateRecord
{
    protected static string $resource = OutboundOperationResource::class;

    protected function afterCreate(): void
    {
        // Record stock movements for shipped items
        $stockService = app(StockMovementService::class);
        
        try {
            $items = $this->record->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'shipped_quantity' => $item->shipped_quantity,
                ];
            })->toArray();
            
            $stockService->recordOutbound($this->record, $items);
            
            Notification::make()
                ->success()
                ->title('Stock Updated')
                ->body('Stock movements have been recorded successfully.')
                ->send();
        } catch (\App\Exceptions\InsufficientStockException $e) {
            Notification::make()
                ->danger()
                ->title('Insufficient Stock')
                ->body('Some items do not have enough stock. Please check inventory.')
                ->persistent()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Stock Update Failed')
                ->body('Failed to record stock movements: ' . $e->getMessage())
                ->persistent()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
