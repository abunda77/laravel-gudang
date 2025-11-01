<?php

namespace App\Observers;

use App\Models\PurchaseOrderItem;

class PurchaseOrderItemObserver
{
    /**
     * Handle the PurchaseOrderItem "created" event.
     */
    public function created(PurchaseOrderItem $purchaseOrderItem): void
    {
        $this->updatePurchaseOrderTotal($purchaseOrderItem);
    }

    /**
     * Handle the PurchaseOrderItem "updated" event.
     */
    public function updated(PurchaseOrderItem $purchaseOrderItem): void
    {
        $this->updatePurchaseOrderTotal($purchaseOrderItem);
    }

    /**
     * Handle the PurchaseOrderItem "deleted" event.
     */
    public function deleted(PurchaseOrderItem $purchaseOrderItem): void
    {
        $this->updatePurchaseOrderTotal($purchaseOrderItem);
    }

    /**
     * Handle the PurchaseOrderItem "restored" event.
     */
    public function restored(PurchaseOrderItem $purchaseOrderItem): void
    {
        $this->updatePurchaseOrderTotal($purchaseOrderItem);
    }

    /**
     * Handle the PurchaseOrderItem "force deleted" event.
     */
    public function forceDeleted(PurchaseOrderItem $purchaseOrderItem): void
    {
        $this->updatePurchaseOrderTotal($purchaseOrderItem);
    }

    /**
     * Update the purchase order total amount.
     */
    private function updatePurchaseOrderTotal(PurchaseOrderItem $purchaseOrderItem): void
    {
        // Only update if the item is already persisted and has a purchase order
        if ($purchaseOrderItem->exists && $purchaseOrderItem->purchase_order_id) {
            $purchaseOrder = $purchaseOrderItem->purchaseOrder;
            if ($purchaseOrder) {
                $purchaseOrder->updateTotalAmount();
            }
        }
    }
}
