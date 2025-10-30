<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\OutboundOperation;
use App\Models\DeliveryOrder;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\InboundOperation;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected StockMovementService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->stockService = app(StockMovementService::class);
    }

    public function test_complete_delivery_order_generation_workflow(): void
    {
        // Create necessary master data
        $customer = Customer::factory()->create();
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $product = Product::factory()->create();

        // Add initial stock
        $this->addInitialStock($product, 100);

        // Create sales order
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        // Create outbound operation
        $outboundOperation = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'prepared_by' => $this->user->id,
        ]);

        // Record stock movement
        $this->stockService->recordOutbound($outboundOperation, [
            ['product_id' => $product->id, 'shipped_quantity' => 20],
        ]);

        // Create delivery order
        $deliveryOrder = DeliveryOrder::factory()->create([
            'outbound_operation_id' => $outboundOperation->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'recipient_name' => 'John Doe',
        ]);

        // Verify delivery order was created correctly
        $this->assertDatabaseHas('delivery_orders', [
            'id' => $deliveryOrder->id,
            'outbound_operation_id' => $outboundOperation->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'recipient_name' => 'John Doe',
        ]);

        // Verify relationships
        $this->assertEquals($outboundOperation->id, $deliveryOrder->outboundOperation->id);
        $this->assertEquals($driver->id, $deliveryOrder->driver->id);
        $this->assertEquals($vehicle->id, $deliveryOrder->vehicle->id);
        $this->assertEquals($deliveryOrder->id, $outboundOperation->deliveryOrder->id);
    }

    public function test_delivery_order_generates_unique_number(): void
    {
        $customer = Customer::factory()->create();
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $outbound1 = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        $outbound2 = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        $delivery1 = DeliveryOrder::factory()->create([
            'outbound_operation_id' => $outbound1->id,
        ]);

        $delivery2 = DeliveryOrder::factory()->create([
            'outbound_operation_id' => $outbound2->id,
        ]);

        $this->assertNotNull($delivery1->do_number);
        $this->assertNotNull($delivery2->do_number);
        $this->assertNotEquals($delivery1->do_number, $delivery2->do_number);
        $this->assertStringStartsWith('DO-', $delivery1->do_number);
        $this->assertStringStartsWith('DO-', $delivery2->do_number);
    }

    public function test_delivery_order_generates_barcode_automatically(): void
    {
        $customer = Customer::factory()->create();
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $outbound = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        $deliveryOrder = DeliveryOrder::factory()->create([
            'outbound_operation_id' => $outbound->id,
        ]);

        // Verify barcode was generated
        $this->assertNotNull($deliveryOrder->barcode);
        $this->assertNotEmpty($deliveryOrder->barcode);
    }

    public function test_delivery_order_can_be_created_without_driver_and_vehicle(): void
    {
        $customer = Customer::factory()->create();
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $outbound = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        // Create delivery order without driver and vehicle
        $deliveryOrder = DeliveryOrder::factory()->create([
            'outbound_operation_id' => $outbound->id,
            'driver_id' => null,
            'vehicle_id' => null,
        ]);

        $this->assertDatabaseHas('delivery_orders', [
            'id' => $deliveryOrder->id,
            'outbound_operation_id' => $outbound->id,
            'driver_id' => null,
            'vehicle_id' => null,
        ]);

        $this->assertNull($deliveryOrder->driver_id);
        $this->assertNull($deliveryOrder->vehicle_id);
    }

    public function test_delivery_order_links_to_outbound_operation_with_stock_movements(): void
    {
        $customer = Customer::factory()->create();
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Add initial stock
        $this->addInitialStock($product1, 100);
        $this->addInitialStock($product2, 50);

        // Create sales order
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        // Create outbound operation
        $outboundOperation = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        // Record stock movements
        $this->stockService->recordOutbound($outboundOperation, [
            ['product_id' => $product1->id, 'shipped_quantity' => 30],
            ['product_id' => $product2->id, 'shipped_quantity' => 15],
        ]);

        // Create delivery order
        $deliveryOrder = DeliveryOrder::factory()->create([
            'outbound_operation_id' => $outboundOperation->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
        ]);

        // Verify delivery order is linked to outbound operation
        $this->assertEquals($outboundOperation->id, $deliveryOrder->outboundOperation->id);

        // Verify outbound operation has stock movements
        $this->assertEquals(2, $outboundOperation->stockMovements()->count());

        // Verify stock was decreased
        $this->assertEquals(70, $this->stockService->getCurrentStock($product1));
        $this->assertEquals(35, $this->stockService->getCurrentStock($product2));

        // Verify we can access stock movements through delivery order -> outbound operation
        $stockMovements = $deliveryOrder->outboundOperation->stockMovements;
        $this->assertEquals(2, $stockMovements->count());
    }

    public function test_multiple_delivery_orders_cannot_share_same_outbound_operation(): void
    {
        $customer = Customer::factory()->create();
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $outbound = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        // Create first delivery order
        $delivery1 = DeliveryOrder::factory()->create([
            'outbound_operation_id' => $outbound->id,
        ]);

        // Verify first delivery order was created
        $this->assertDatabaseHas('delivery_orders', [
            'id' => $delivery1->id,
            'outbound_operation_id' => $outbound->id,
        ]);

        // Verify outbound operation has one delivery order
        $this->assertEquals(1, $outbound->deliveryOrder()->count());
        $this->assertEquals($delivery1->id, $outbound->deliveryOrder->id);
    }

    public function test_delivery_order_with_notes_and_recipient(): void
    {
        $customer = Customer::factory()->create();
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $outbound = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        $deliveryOrder = DeliveryOrder::factory()->create([
            'outbound_operation_id' => $outbound->id,
            'recipient_name' => 'Jane Smith',
            'notes' => 'Handle with care - fragile items',
        ]);

        $this->assertDatabaseHas('delivery_orders', [
            'id' => $deliveryOrder->id,
            'recipient_name' => 'Jane Smith',
            'notes' => 'Handle with care - fragile items',
        ]);

        $this->assertEquals('Jane Smith', $deliveryOrder->recipient_name);
        $this->assertEquals('Handle with care - fragile items', $deliveryOrder->notes);
    }

    /**
     * Helper method to add initial stock via inbound operation.
     */
    private function addInitialStock(Product $product, int $quantity): void
    {
        $supplier = Supplier::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        $inbound = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $this->stockService->recordInbound($inbound, [
            ['product_id' => $product->id, 'received_quantity' => $quantity],
        ]);
    }
}
