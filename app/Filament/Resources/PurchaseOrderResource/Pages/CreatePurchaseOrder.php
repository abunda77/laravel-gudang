<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set created_by to current user
        $data['created_by'] = auth()->id();
        
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
