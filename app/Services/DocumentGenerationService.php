<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentGenerationService
{
    /**
     * Generate delivery order PDF
     *
     * @param DeliveryOrder $deliveryOrder
     * @return string PDF output as string
     * @throws \RuntimeException
     */
    public function generateDeliveryOrder(DeliveryOrder $deliveryOrder): string
    {
        try {
            $pdf = Pdf::loadView('documents.delivery-order', [
                'deliveryOrder' => $deliveryOrder->load([
                    'outboundOperation.items.product',
                    'outboundOperation.salesOrder.customer',
                    'driver',
                    'vehicle'
                ])
            ]);

            Log::info('Delivery order PDF generated successfully', [
                'delivery_order_id' => $deliveryOrder->id,
                'do_number' => $deliveryOrder->do_number,
            ]);

            return $pdf->output();
        } catch (\Throwable $e) {
            Log::error('Failed to generate delivery order PDF', [
                'delivery_order_id' => $deliveryOrder->id,
                'do_number' => $deliveryOrder->do_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate delivery order PDF: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate invoice PDF
     *
     * @param Invoice $invoice
     * @return string PDF output as string
     * @throws \RuntimeException
     */
    public function generateInvoice(Invoice $invoice): string
    {
        try {
            $pdf = Pdf::loadView('documents.invoice', [
                'invoice' => $invoice->load([
                    'salesOrder.items.product',
                    'salesOrder.customer'
                ])
            ]);

            Log::info('Invoice PDF generated successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);

            return $pdf->output();
        } catch (\Throwable $e) {
            Log::error('Failed to generate invoice PDF', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate invoice PDF: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate barcode for delivery order tracking
     *
     * @param string $doNumber
     * @return string Base64 encoded barcode image
     * @throws \RuntimeException
     */
    public function generateBarcode(string $doNumber): string
    {
        try {
            // Generate a simple barcode using SVG
            // This creates a Code 128-style barcode representation
            $svg = $this->generateBarcodeSvg($doNumber);
            
            return base64_encode($svg);
        } catch (\Throwable $e) {
            Log::error('Failed to generate barcode', [
                'do_number' => $doNumber,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to generate barcode: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate SVG barcode representation
     *
     * @param string $text
     * @return string SVG content
     */
    private function generateBarcodeSvg(string $text): string
    {
        $width = 300;
        $height = 80;
        $barWidth = 2;
        
        // Simple encoding: convert each character to binary pattern
        $encoded = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $encoded .= str_pad(decbin(ord($text[$i])), 8, '0', STR_PAD_LEFT);
        }
        
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="' . $width . '" height="' . $height . '" fill="white"/>';
        
        $x = 10;
        for ($i = 0; $i < strlen($encoded) && $x < $width - 10; $i++) {
            if ($encoded[$i] === '1') {
                $svg .= '<rect x="' . $x . '" y="10" width="' . $barWidth . '" height="50" fill="black"/>';
            }
            $x += $barWidth;
        }
        
        // Add text below barcode
        $svg .= '<text x="' . ($width / 2) . '" y="' . ($height - 10) . '" font-family="monospace" font-size="12" text-anchor="middle">' . htmlspecialchars($text) . '</text>';
        $svg .= '</svg>';
        
        return $svg;
    }
}
