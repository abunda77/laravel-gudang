<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Calculate total amount from items
        $totalAmount = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $totalAmount += ($item['ordered_quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            }
        }
        $data['total_amount'] = $totalAmount;
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
