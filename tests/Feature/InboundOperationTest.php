<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\InboundOperation;
use App\Models\InboundOperationItem;
use App\Models\StockMovement;
use App\Enums\StockMovementType;
use App\Enums\PurchaseOrderStatus;
use App\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InboundOperationTest extends TestCase
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

    public function test_complete_inbound_workflow_from_po_to_stock_increase(): void
    {
        // Create supplier and products
        $supplier = Supplier::factory()->create();
        $product1 = Product::factory()->create(['sku' => 'TEST-001']);
        $product2 = Product::factory()->create(['sku' => 'TEST-002']);

        // Verify initial stock is zero
        $this->assertEquals(0, $this->stockService->getCurrentStock($product1));
        $this->assertEquals(0, $this->stockService->getCurrentStock($product2));

        // Create purchase order
        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::SENT,
        ]);

        // Create inbound operation
        $inboundOperation = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'received_by' => $this->user->id,
        ]);

        // Create inbound operation items
        $items = [
            [
                'product_id' => $product1->id,
                'ordered_quantity' => 100,
                'received_quantity' => 100,
            ],
            [
                'product_id' => $product2->id,
                'ordered_quantity' => 50,
                'received_quantity' => 45, // Received less than ordered
            ],
        ];

        foreach ($items as $itemData) {
            InboundOperationItem::factory()->create([
                'inbound_operation_id' => $inboundOperation->id,
                'product_id' => $itemData['product_id'],
                'ordered_quantity' => $itemData['ordered_quantity'],
                'received_quantity' => $itemData['received_quantity'],
            ]);
        }

        // Record stock movements
        $this->stockService->recordInbound($inboundOperation, $items);

        // Verify stock increased correctly
        $this->assertEquals(100, $this->stockService->getCurrentStock($product1));
        $this->assertEquals(45, $this->stockService->getCurrentStock($product2));

        // Verify stock movements were created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product1->id,
            'quantity' => 100,
            'type' => StockMovementType::INBOUND->value,
            'reference_type' => InboundOperation::class,
            'reference_id' => $inboundOperation->id,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product2->id,
            'quantity' => 45,
            'type' => StockMovementType::INBOUND->value,
            'reference_type' => InboundOperation::class,
            'reference_id' => $inboundOperation->id,
        ]);

        // Verify relationships
        $this->assertEquals(2, $inboundOperation->items()->count());
        $this->assertEquals(2, $inboundOperation->stockMovements()->count());
        $this->assertEquals($purchaseOrder->id, $inboundOperation->purchaseOrder->id);
    }

    public function test_inbound_operation_generates_unique_number(): void
    {
        $supplier = Supplier::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        $inbound1 = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $inbound2 = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $this->assertNotNull($inbound1->inbound_number);
        $this->assertNotNull($inbound2->inbound_number);
        $this->assertNotEquals($inbound1->inbound_number, $inbound2->inbound_number);
        $this->assertStringStartsWith('IN-', $inbound1->inbound_number);
        $this->assertStringStartsWith('IN-', $inbound2->inbound_number);
    }

    public function test_multiple_inbound_operations_accumulate_stock(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        // First inbound operation
        $inbound1 = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $this->stockService->recordInbound($inbound1, [
            ['product_id' => $product->id, 'received_quantity' => 50],
        ]);

        $this->assertEquals(50, $this->stockService->getCurrentStock($product));

        // Second inbound operation
        $inbound2 = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $this->stockService->recordInbound($inbound2, [
            ['product_id' => $product->id, 'received_quantity' => 30],
        ]);

        // Stock should accumulate
        $this->assertEquals(80, $this->stockService->getCurrentStock($product));
    }

    public function test_inbound_operation_with_zero_quantity_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        $inbound = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $this->stockService->recordInbound($inbound, [
            ['product_id' => $product->id, 'received_quantity' => 0],
        ]);
    }

    public function test_inbound_operation_with_empty_items_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $supplier = Supplier::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        $inbound = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $this->stockService->recordInbound($inbound, []);
    }
}
