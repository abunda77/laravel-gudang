<?php

namespace App\Observers;

use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PurchaseOrderItemObserver
{
    /**
     * Handle the PurchaseOrderItem "created" event.
     */
    public function created(PurchaseOrderItem $purchaseOrderItem): void
    {
        $this->updatePurchaseOrderTotal($purchaseOrderItem);
        $this->sendWebhookNotification($purchaseOrderItem);
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

    /**
     * Send webhook notification to n8n.
     */
    private function sendWebhookNotification(PurchaseOrderItem $purchaseOrderItem): void
    {
        $webhookUrl = config('app.webhook_wa_n8n_purchaseorder');

        if (empty($webhookUrl)) {
            return;
        }

        try {
            $purchaseOrder = $purchaseOrderItem->purchaseOrder;
            $product = $purchaseOrderItem->product;
            $variant = $purchaseOrderItem->productVariant;

            $payload = [
                'event' => 'purchase_order_item_created',
                'timestamp' => now()->toIso8601String(),
                'data' => [
                    'item_id' => $purchaseOrderItem->id,
                    'purchase_order' => [
                        'id' => $purchaseOrder->id,
                        'order_number' => $purchaseOrder->order_number,
                        'status' => $purchaseOrder->status->value,
                        'order_date' => $purchaseOrder->order_date->format('Y-m-d'),
                        'supplier' => [
                            'id' => $purchaseOrder->supplier->id,
                            'name' => $purchaseOrder->supplier->name,
                        ],
                        'total_amount' => $purchaseOrder->total_amount,
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
                    'quantity' => $purchaseOrderItem->quantity,
                    'unit_price' => $purchaseOrderItem->unit_price,
                    'subtotal' => $purchaseOrderItem->subtotal,
                    'notes' => $purchaseOrderItem->notes,
                ],
            ];

            Http::timeout(5)->post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error('Failed to send webhook notification for PurchaseOrderItem', [
                'item_id' => $purchaseOrderItem->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
