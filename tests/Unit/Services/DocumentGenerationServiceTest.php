<?php

namespace Tests\Unit\Services;

use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\OutboundOperation;
use App\Models\OutboundOperationItem;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Vehicle;
use App\Services\DocumentGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentGenerationServiceTest extends TestCase
{
    use RefreshDatabase;

    private DocumentGenerationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DocumentGenerationService();
    }

    /** @test */
    public function it_generates_delivery_order_pdf()
    {
        $customer = Customer::factory()->create();
        $salesOrder = SalesOrder::factory()->create(['customer_id' => $customer->id]);
        $product = Product::factory()->create();
        
        SalesOrderItem::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $outbound = OutboundOperation::factory()->create(['sales_order_id' => $salesOrder->id]);
        
        OutboundOperationItem::factory()->create([
            'outbound_operation_id' => $outbound->id,
            'product_id' => $product->id,
            'shipped_quantity' => 10,
        ]);

        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $deliveryOrder = DeliveryOrder::factory()->create([
            'outbound_operation_id' => $outbound->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $pdf = $this->service->generateDeliveryOrder($deliveryOrder);

        $this->assertIsString($pdf);
        $this->assertNotEmpty($pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    /** @test */
    public function it_generates_invoice_pdf()
    {
        $customer = Customer::factory()->create();
        $salesOrder = SalesOrder::factory()->create(['customer_id' => $customer->id]);
        $product = Product::factory()->create();
        
        SalesOrderItem::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $invoice = Invoice::factory()->create(['sales_order_id' => $salesOrder->id]);

        $pdf = $this->service->generateInvoice($invoice);

        $this->assertIsString($pdf);
        $this->assertNotEmpty($pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    /** @test */
    public function it_generates_barcode()
    {
        $doNumber = 'DO-20241030-0001';

        $barcode = $this->service->generateBarcode($doNumber);

        $this->assertIsString($barcode);
        $this->assertNotEmpty($barcode);
        
        // Verify it's base64 encoded
        $decoded = base64_decode($barcode, true);
        $this->assertNotFalse($decoded);
        
        // Verify it contains SVG content
        $this->assertStringContainsString('<svg', $decoded);
        $this->assertStringContainsString($doNumber, $decoded);
    }

    /** @test */
    public function it_throws_exception_for_invalid_delivery_order()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to generate delivery order PDF');

        // Create delivery order without required relationships
        $deliveryOrder = new DeliveryOrder();
        $deliveryOrder->id = 999999;
        $deliveryOrder->do_number = 'DO-TEST-0001';

        $this->service->generateDeliveryOrder($deliveryOrder);
    }

    /** @test */
    public function it_throws_exception_for_invalid_invoice()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to generate invoice PDF');

        // Create invoice without required relationships
        $invoice = new Invoice();
        $invoice->id = 999999;
        $invoice->invoice_number = 'INV-TEST-0001';

        $this->service->generateInvoice($invoice);
    }
}
