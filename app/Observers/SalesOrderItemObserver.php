<?php

namespace App\Observers;

use App\Models\SalesOrderItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesOrderItemObserver
{
    /**
     * Handle the SalesOrderItem "created" event.
     */
    public function created(SalesOrderItem $salesOrderItem): void
    {
        $this->updateSalesOrderTotal($salesOrderItem);
        $this->sendWebhookNotification($salesOrderItem);
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

    /**
     * Send webhook notification to n8n.
     */
    private function sendWebhookNotification(SalesOrderItem $salesOrderItem): void
    {
        $webhookUrl = config('app.webhook_wa_n8n_salesorder');

        if (empty($webhookUrl)) {
            return;
        }

        try {
            $salesOrder = $salesOrderItem->salesOrder;
            $product = $salesOrderItem->product;
            $variant = $salesOrderItem->productVariant;

            $payload = [
                'event' => 'sales_order_item_created',
                'timestamp' => now()->toIso8601String(),
                'data' => [
                    'item_id' => $salesOrderItem->id,
                    'sales_order' => [
                        'id' => $salesOrder->id,
                        'order_number' => $salesOrder->order_number,
                        'status' => $salesOrder->status->value,
                        'order_date' => $salesOrder->order_date->format('Y-m-d'),
                        'customer' => [
                            'id' => $salesOrder->customer->id,
                            'name' => $salesOrder->customer->name,
                        ],
                        'total_amount' => $salesOrder->total_amount,
                    ],
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                    ],
                    'variant' => $variant ? [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                    ] : null,
                    'quantity' => $salesOrderItem->quantity,
                    'unit_price' => $salesOrderItem->unit_price,
                    'subtotal' => $salesOrderItem->subtotal,
                    'notes' => $salesOrderItem->notes,
                ],
            ];

            Http::timeout(5)->post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error('Failed to send webhook notification for SalesOrderItem', [
                'item_id' => $salesOrderItem->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
