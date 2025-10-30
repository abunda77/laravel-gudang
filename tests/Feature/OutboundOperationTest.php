<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\OutboundOperation;
use App\Models\OutboundOperationItem;
use App\Models\StockMovement;
use App\Models\InboundOperation;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Enums\StockMovementType;
use App\Enums\SalesOrderStatus;
use App\Services\StockMovementService;
use App\Exceptions\InsufficientStockException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OutboundOperationTest extends TestCase
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

    public function test_complete_outbound_workflow_from_so_to_stock_decrease(): void
    {
        // Create customer and products
        $customer = Customer::factory()->create();
        $product1 = Product::factory()->create(['sku' => 'OUT-001']);
        $product2 = Product::factory()->create(['sku' => 'OUT-002']);

        // Add initial stock via inbound operation
        $this->addInitialStock($product1, 100);
        $this->addInitialStock($product2, 50);

        // Verify initial stock
        $this->assertEquals(100, $this->stockService->getCurrentStock($product1));
        $this->assertEquals(50, $this->stockService->getCurrentStock($product2));

        // Create sales order
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::APPROVED,
            'sales_user_id' => $this->user->id,
        ]);

        // Create outbound operation
        $outboundOperation = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'prepared_by' => $this->user->id,
        ]);

        // Create outbound operation items
        $items = [
            [
                'product_id' => $product1->id,
                'shipped_quantity' => 30,
            ],
            [
                'product_id' => $product2->id,
                'shipped_quantity' => 20,
            ],
        ];

        foreach ($items as $itemData) {
            OutboundOperationItem::factory()->create([
                'outbound_operation_id' => $outboundOperation->id,
                'product_id' => $itemData['product_id'],
                'shipped_quantity' => $itemData['shipped_quantity'],
            ]);
        }

        // Record stock movements
        $this->stockService->recordOutbound($outboundOperation, $items);

        // Verify stock decreased correctly
        $this->assertEquals(70, $this->stockService->getCurrentStock($product1));
        $this->assertEquals(30, $this->stockService->getCurrentStock($product2));

        // Verify stock movements were created with negative quantities
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product1->id,
            'quantity' => -30,
            'type' => StockMovementType::OUTBOUND->value,
            'reference_type' => OutboundOperation::class,
            'reference_id' => $outboundOperation->id,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product2->id,
            'quantity' => -20,
            'type' => StockMovementType::OUTBOUND->value,
            'reference_type' => OutboundOperation::class,
            'reference_id' => $outboundOperation->id,
        ]);

        // Verify relationships
        $this->assertEquals(2, $outboundOperation->items()->count());
        $this->assertEquals(2, $outboundOperation->stockMovements()->count());
        $this->assertEquals($salesOrder->id, $outboundOperation->salesOrder->id);
    }

    public function test_outbound_operation_generates_unique_number(): void
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

        $this->assertNotNull($outbound1->outbound_number);
        $this->assertNotNull($outbound2->outbound_number);
        $this->assertNotEquals($outbound1->outbound_number, $outbound2->outbound_number);
        $this->assertStringStartsWith('OUT-', $outbound1->outbound_number);
        $this->assertStringStartsWith('OUT-', $outbound2->outbound_number);
    }

    public function test_outbound_operation_throws_exception_when_insufficient_stock(): void
    {
        $this->expectException(InsufficientStockException::class);

        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        // Add only 10 units of stock
        $this->addInitialStock($product, 10);

        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $outbound = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        // Try to ship 20 units (more than available)
        $this->stockService->recordOutbound($outbound, [
            ['product_id' => $product->id, 'shipped_quantity' => 20],
        ]);
    }

    public function test_outbound_operation_does_not_modify_stock_when_insufficient(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        // Add only 10 units of stock
        $this->addInitialStock($product, 10);

        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $outbound = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        try {
            // Try to ship 20 units (more than available)
            $this->stockService->recordOutbound($outbound, [
                ['product_id' => $product->id, 'shipped_quantity' => 20],
            ]);
        } catch (InsufficientStockException $e) {
            // Expected exception
        }

        // Stock should remain unchanged
        $this->assertEquals(10, $this->stockService->getCurrentStock($product));

        // No outbound stock movement should be created
        $this->assertDatabaseMissing('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovementType::OUTBOUND->value,
            'reference_type' => OutboundOperation::class,
            'reference_id' => $outbound->id,
        ]);
    }

    public function test_multiple_outbound_operations_decrease_stock_correctly(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        // Add initial stock
        $this->addInitialStock($product, 100);

        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        // First outbound operation
        $outbound1 = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        $this->stockService->recordOutbound($outbound1, [
            ['product_id' => $product->id, 'shipped_quantity' => 30],
        ]);

        $this->assertEquals(70, $this->stockService->getCurrentStock($product));

        // Second outbound operation
        $outbound2 = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        $this->stockService->recordOutbound($outbound2, [
            ['product_id' => $product->id, 'shipped_quantity' => 20],
        ]);

        // Stock should decrease cumulatively
        $this->assertEquals(50, $this->stockService->getCurrentStock($product));
    }

    public function test_outbound_operation_with_zero_quantity_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $this->addInitialStock($product, 100);

        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $outbound = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        $this->stockService->recordOutbound($outbound, [
            ['product_id' => $product->id, 'shipped_quantity' => 0],
        ]);
    }

    public function test_outbound_operation_with_empty_items_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $customer = Customer::factory()->create();
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $outbound = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        $this->stockService->recordOutbound($outbound, []);
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
