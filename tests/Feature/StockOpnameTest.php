<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StockMovement;
use App\Models\InboundOperation;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Enums\StockMovementType;
use App\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockOpnameTest extends TestCase
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

    public function test_complete_stock_opname_workflow_with_surplus(): void
    {
        // Create product and add initial stock
        $product = Product::factory()->create(['sku' => 'OPN-001']);
        $this->addInitialStock($product, 100);

        // Verify system stock
        $systemStock = $this->stockService->getCurrentStock($product);
        $this->assertEquals(100, $systemStock);

        // Create stock opname
        $stockOpname = StockOpname::factory()->create([
            'created_by' => $this->user->id,
        ]);

        // Physical count shows 110 units (10 more than system)
        $physicalStock = 110;
        $variance = $physicalStock - $systemStock;

        // Create stock opname item
        StockOpnameItem::factory()->create([
            'stock_opname_id' => $stockOpname->id,
            'product_id' => $product->id,
            'system_stock' => $systemStock,
            'physical_stock' => $physicalStock,
            'variance' => $variance,
        ]);

        // Record adjustment
        $this->stockService->recordAdjustment($stockOpname, $product, $variance);

        // Verify stock increased to match physical count
        $this->assertEquals(110, $this->stockService->getCurrentStock($product));

        // Verify adjustment stock movement was created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => 10,
            'type' => StockMovementType::ADJUSTMENT_PLUS->value,
            'reference_type' => StockOpname::class,
            'reference_id' => $stockOpname->id,
        ]);

        // Verify relationships
        $this->assertEquals(1, $stockOpname->items()->count());
        $this->assertEquals(1, $stockOpname->stockMovements()->count());
    }

    public function test_complete_stock_opname_workflow_with_shortage(): void
    {
        // Create product and add initial stock
        $product = Product::factory()->create(['sku' => 'OPN-002']);
        $this->addInitialStock($product, 100);

        // Verify system stock
        $systemStock = $this->stockService->getCurrentStock($product);
        $this->assertEquals(100, $systemStock);

        // Create stock opname
        $stockOpname = StockOpname::factory()->create([
            'created_by' => $this->user->id,
        ]);

        // Physical count shows 85 units (15 less than system)
        $physicalStock = 85;
        $variance = $physicalStock - $systemStock;

        // Create stock opname item
        StockOpnameItem::factory()->create([
            'stock_opname_id' => $stockOpname->id,
            'product_id' => $product->id,
            'system_stock' => $systemStock,
            'physical_stock' => $physicalStock,
            'variance' => $variance,
        ]);

        // Record adjustment
        $this->stockService->recordAdjustment($stockOpname, $product, $variance);

        // Verify stock decreased to match physical count
        $this->assertEquals(85, $this->stockService->getCurrentStock($product));

        // Verify adjustment stock movement was created with negative quantity
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => -15,
            'type' => StockMovementType::ADJUSTMENT_MINUS->value,
            'reference_type' => StockOpname::class,
            'reference_id' => $stockOpname->id,
        ]);
    }

    public function test_stock_opname_with_zero_variance_does_not_create_movement(): void
    {
        // Create product and add initial stock
        $product = Product::factory()->create();
        $this->addInitialStock($product, 100);

        // Verify system stock
        $systemStock = $this->stockService->getCurrentStock($product);
        $this->assertEquals(100, $systemStock);

        // Create stock opname
        $stockOpname = StockOpname::factory()->create([
            'created_by' => $this->user->id,
        ]);

        // Physical count matches system stock exactly
        $physicalStock = 100;
        $variance = 0;

        // Create stock opname item
        StockOpnameItem::factory()->create([
            'stock_opname_id' => $stockOpname->id,
            'product_id' => $product->id,
            'system_stock' => $systemStock,
            'physical_stock' => $physicalStock,
            'variance' => $variance,
        ]);

        // Record adjustment (should do nothing)
        $this->stockService->recordAdjustment($stockOpname, $product, $variance);

        // Verify stock remains unchanged
        $this->assertEquals(100, $this->stockService->getCurrentStock($product));

        // Verify no adjustment stock movement was created
        $this->assertDatabaseMissing('stock_movements', [
            'product_id' => $product->id,
            'reference_type' => StockOpname::class,
            'reference_id' => $stockOpname->id,
        ]);
    }

    public function test_stock_opname_generates_unique_number(): void
    {
        $opname1 = StockOpname::factory()->create();
        $opname2 = StockOpname::factory()->create();

        $this->assertNotNull($opname1->opname_number);
        $this->assertNotNull($opname2->opname_number);
        $this->assertNotEquals($opname1->opname_number, $opname2->opname_number);
        $this->assertStringStartsWith('OPN-', $opname1->opname_number);
        $this->assertStringStartsWith('OPN-', $opname2->opname_number);
    }

    public function test_stock_opname_with_multiple_products(): void
    {
        // Create multiple products with initial stock
        $product1 = Product::factory()->create(['sku' => 'MULTI-001']);
        $product2 = Product::factory()->create(['sku' => 'MULTI-002']);
        $product3 = Product::factory()->create(['sku' => 'MULTI-003']);

        $this->addInitialStock($product1, 100);
        $this->addInitialStock($product2, 50);
        $this->addInitialStock($product3, 75);

        // Create stock opname
        $stockOpname = StockOpname::factory()->create([
            'created_by' => $this->user->id,
        ]);

        // Define physical counts and variances
        $adjustments = [
            ['product' => $product1, 'system' => 100, 'physical' => 105, 'variance' => 5],
            ['product' => $product2, 'system' => 50, 'physical' => 48, 'variance' => -2],
            ['product' => $product3, 'system' => 75, 'physical' => 75, 'variance' => 0],
        ];

        foreach ($adjustments as $adjustment) {
            StockOpnameItem::factory()->create([
                'stock_opname_id' => $stockOpname->id,
                'product_id' => $adjustment['product']->id,
                'system_stock' => $adjustment['system'],
                'physical_stock' => $adjustment['physical'],
                'variance' => $adjustment['variance'],
            ]);

            // Record adjustment
            $this->stockService->recordAdjustment(
                $stockOpname,
                $adjustment['product'],
                $adjustment['variance']
            );
        }

        // Verify final stock for each product
        $this->assertEquals(105, $this->stockService->getCurrentStock($product1));
        $this->assertEquals(48, $this->stockService->getCurrentStock($product2));
        $this->assertEquals(75, $this->stockService->getCurrentStock($product3));

        // Verify stock opname has 3 items
        $this->assertEquals(3, $stockOpname->items()->count());

        // Verify only 2 stock movements were created (zero variance doesn't create movement)
        $this->assertEquals(2, $stockOpname->stockMovements()->count());
    }

    public function test_stock_opname_adjustments_are_reflected_in_subsequent_operations(): void
    {
        // Create product and add initial stock
        $product = Product::factory()->create();
        $this->addInitialStock($product, 100);

        // Perform stock opname with surplus
        $stockOpname = StockOpname::factory()->create();
        $this->stockService->recordAdjustment($stockOpname, $product, 20);

        // Stock should now be 120
        $this->assertEquals(120, $this->stockService->getCurrentStock($product));

        // Perform another inbound operation
        $this->addInitialStock($product, 30);

        // Stock should accumulate correctly
        $this->assertEquals(150, $this->stockService->getCurrentStock($product));

        // Verify all stock movements
        $movements = StockMovement::where('product_id', $product->id)
            ->orderBy('created_at')
            ->get();

        $this->assertEquals(3, $movements->count());
        $this->assertEquals(100, $movements[0]->quantity); // Initial inbound
        $this->assertEquals(20, $movements[1]->quantity);  // Adjustment
        $this->assertEquals(30, $movements[2]->quantity);  // Second inbound
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
