<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
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
            $pdf = Pdf::loadView('invoices.pdf', [
                'invoice' => $invoice->load([
                    'salesOrder.items.product',
                    'salesOrder.items.productVariant',
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
     * Generate product list PDF
     *
     * @param Collection|LengthAwarePaginator $products
     * @return string PDF output as string
     * @throws \RuntimeException
     */
    public function generateProductList(Collection|LengthAwarePaginator $products): string
    {
        try {
            // Convert paginator to collection if needed
            $productCollection = $products instanceof LengthAwarePaginator ? $products->getCollection() : $products;

            $pdf = Pdf::loadView('reports.product-list-pdf', [
                'products' => $productCollection->load(['category']),
                'generatedAt' => now(),
                'totalProducts' => $productCollection->count(),
                'totalValue' => $productCollection->sum(function ($product) {
                    return $product->getCurrentStock() * $product->purchase_price;
                }),
            ]);

            Log::info('Product list PDF generated successfully', [
                'total_products' => $productCollection->count(),
            ]);

            return $pdf->output();
        } catch (\Throwable $e) {
            Log::error('Failed to generate product list PDF', [
                'total_products' => $productCollection->count(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate product list PDF: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate customer list PDF
     *
     * @param Collection|LengthAwarePaginator $customers
     * @return string PDF output as string
     * @throws \RuntimeException
     */
    public function generateCustomerList(Collection|LengthAwarePaginator $customers): string
    {
        try {
            // Convert paginator to collection if needed
            $customerCollection = $customers instanceof LengthAwarePaginator ? $customers->getCollection() : $customers;

            $pdf = Pdf::loadView('reports.customer-list-pdf', [
                'customers' => $customerCollection->load(['salesOrders']),
                'generatedAt' => now(),
                'totalCustomers' => $customerCollection->count(),
            ]);

            Log::info('Customer list PDF generated successfully', [
                'total_customers' => $customerCollection->count(),
            ]);

            return $pdf->output();
        } catch (\Throwable $e) {
            Log::error('Failed to generate customer list PDF', [
                'total_customers' => $customerCollection->count(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate customer list PDF: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate supplier list PDF
     *
     * @param Collection|LengthAwarePaginator $suppliers
     * @return string PDF output as string
     * @throws \RuntimeException
     */
    public function generateSupplierList(Collection|LengthAwarePaginator $suppliers): string
    {
        try {
            // Convert paginator to collection if needed
            $supplierCollection = $suppliers instanceof LengthAwarePaginator ? $suppliers->getCollection() : $suppliers;

            $pdf = Pdf::loadView('reports.supplier-list-pdf', [
                'suppliers' => $supplierCollection->load(['purchaseOrders']),
                'generatedAt' => now(),
                'totalSuppliers' => $supplierCollection->count(),
            ]);

            Log::info('Supplier list PDF generated successfully', [
                'total_suppliers' => $supplierCollection->count(),
            ]);

            return $pdf->output();
        } catch (\Throwable $e) {
            Log::error('Failed to generate supplier list PDF', [
                'total_suppliers' => $supplierCollection->count(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate supplier list PDF: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate driver list PDF
     *
     * @param Collection|LengthAwarePaginator $drivers
     * @return string PDF output as string
     * @throws \RuntimeException
     */
    public function generateDriverList(Collection|LengthAwarePaginator $drivers): string
    {
        try {
            // Convert paginator to collection if needed
            $driverCollection = $drivers instanceof LengthAwarePaginator ? $drivers->getCollection() : $drivers;

            $pdf = Pdf::loadView('reports.driver-list-pdf', [
                'drivers' => $driverCollection->load(['deliveryOrders']),
                'generatedAt' => now(),
                'totalDrivers' => $driverCollection->count(),
            ]);

            Log::info('Driver list PDF generated successfully', [
                'total_drivers' => $driverCollection->count(),
            ]);

            return $pdf->output();
        } catch (\Throwable $e) {
            Log::error('Failed to generate driver list PDF', [
                'total_drivers' => $driverCollection->count(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate driver list PDF: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate vehicle list PDF
     *
     * @param Collection|LengthAwarePaginator $vehicles
     * @return string PDF output as string
     * @throws \RuntimeException
     */
    public function generateVehicleList(Collection|LengthAwarePaginator $vehicles): string
    {
        try {
            // Convert paginator to collection if needed
            $vehicleCollection = $vehicles instanceof LengthAwarePaginator ? $vehicles->getCollection() : $vehicles;

            $pdf = Pdf::loadView('reports.vehicle-list-pdf', [
                'vehicles' => $vehicleCollection->load(['deliveryOrders']),
                'generatedAt' => now(),
                'totalVehicles' => $vehicleCollection->count(),
            ]);

            Log::info('Vehicle list PDF generated successfully', [
                'total_vehicles' => $vehicleCollection->count(),
            ]);

            return $pdf->output();
        } catch (\Throwable $e) {
            Log::error('Failed to generate vehicle list PDF', [
                'total_vehicles' => $vehicleCollection->count(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate vehicle list PDF: ' . $e->getMessage(), 0, $e);
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
