<?php

namespace App\Filament\Resources\OutboundOperationResource\Pages;

use App\Filament\Resources\OutboundOperationResource;
use App\Services\StockMovementService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOutboundOperation extends EditRecord
{
    protected static string $resource = OutboundOperationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    // Delete associated stock movements when deleting outbound operation
                    $this->record->stockMovements()->delete();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Update the outbound operation
        $record->update([
            'sales_order_id' => $data['sales_order_id'],
            'shipped_date' => $data['shipped_date'],
            'notes' => $data['notes'] ?? null,
            'prepared_by' => $data['prepared_by'],
        ]);

        // Update items
        if (isset($data['items']) && !empty($data['items'])) {
            // Delete existing items
            $record->items()->delete();

            // Create new items
            foreach ($data['items'] as $item) {
                $record->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'shipped_quantity' => $item['shipped_quantity'],
                ]);
            }

            // Delete existing stock movements
            $record->stockMovements()->delete();

            // Record new stock movements
            try {
                $stockService = app(StockMovementService::class);
                $stockService->recordOutbound($record, $data['items']);

                Notification::make()
                    ->success()
                    ->title('Outbound operation updated')
                    ->body('Stock movements have been updated successfully.')
                    ->send();
            } catch (\App\Exceptions\InsufficientStockException $e) {
                Notification::make()
                    ->danger()
                    ->title('Insufficient stock')
                    ->body('Some items do not have enough stock: ' . collect($e->getUnavailableItems())->pluck('product_name')->join(', '))
                    ->persistent()
                    ->send();

                $this->halt();
            } catch (\Exception $e) {
                Notification::make()
                    ->danger()
                    ->title('Failed to record stock movements')
                    ->body($e->getMessage())
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }

        return $record;
    }
}
