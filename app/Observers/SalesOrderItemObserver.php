<?php

namespace App\Observers;

use App\Models\SalesOrderItem;

class SalesOrderItemObserver
{
    /**
     * Handle the SalesOrderItem "created" event.
     */
    public function created(SalesOrderItem $salesOrderItem): void
    {
        $this->updateSalesOrderTotal($salesOrderItem);
    }

    /**
     * Handle the SalesOrderItem "updated" event.
     */
    public function updated(SalesOrderItem $salesOrderItem): void
    {
        $this->updateSalesOrderTotal($salesOrderItem);
    }

    /**
     * Handle the SalesOrderItem "deleted" event.
     */
    public function deleted(SalesOrderItem $salesOrderItem): void
    {
        $this->updateSalesOrderTotal($salesOrderItem);
    }

    /**
     * Handle the SalesOrderItem "restored" event.
     */
    public function restored(SalesOrderItem $salesOrderItem): void
    {
        $this->updateSalesOrderTotal($salesOrderItem);
    }

    /**
     * Handle the SalesOrderItem "force deleted" event.
     */
    public function forceDeleted(SalesOrderItem $salesOrderItem): void
    {
        $this->updateSalesOrderTotal($salesOrderItem);
    }

    /**
     * Update the sales order total amount.
     */
    private function updateSalesOrderTotal(SalesOrderItem $salesOrderItem): void
    {
        // Only update if the item is already persisted and has a sales order
        if ($salesOrderItem->exists && $salesOrderItem->sales_order_id) {
            $salesOrder = $salesOrderItem->salesOrder;
            if ($salesOrder) {
                $salesOrder->updateTotalAmount();
            }
        }
    }
}
