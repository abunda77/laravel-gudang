<?php

namespace Tests\Unit\Services;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\InboundOperation;
use App\Models\OutboundOperation;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\User;
use App\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StockMovementServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockMovementService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockMovementService();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_records_inbound_movement_correctly()
    {
        $product = Product::factory()->create();
        $inbound = InboundOperation::factory()->create();

        $items = [
            [
                'product_id' => $product->id,
                'received_quantity' => 10,
            ],
        ];

        $this->service->recordInbound($inbound, $items);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => 10,
            'type' => StockMovementType::INBOUND->value,
            'reference_type' => InboundOperation::class,
            'reference_id' => $inbound->id,
        ]);

        $this->assertEquals(10, $this->service->getCurrentStock($product));
    }

    /** @test */
    public function it_records_multiple_inbound_items()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $inbound = InboundOperation::factory()->create();

        $items = [
            ['product_id' => $product1->id, 'received_quantity' => 10],
            ['product_id' => $product2->id, 'received_quantity' => 20],
        ];

        $this->service->recordInbound($inbound, $items);

        $this->assertEquals(10, $this->service->getCurrentStock($product1));
        $this->assertEquals(20, $this->service->getCurrentStock($product2));
    }

    /** @test */
    public function it_throws_exception_for_empty_inbound_items()
    {
        $inbound = InboundOperation::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Items array cannot be empty');

        $this->service->recordInbound($inbound, []);
    }

    /** @test */
    public function it_throws_exception_for_invalid_inbound_quantity()
    {
        $product = Product::factory()->create();
        $inbound = InboundOperation::factory()->create();

        $items = [
            ['product_id' => $product->id, 'received_quantity' => 0],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Received quantity must be greater than zero');

        $this->service->recordInbound($inbound, $items);
    }

    /** @test */
    public function it_records_outbound_movement_correctly()
    {
        $product = Product::factory()->create();
        
        // Create initial stock
        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 50,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        Cache::forget("product_stock_{$product->id}");

        $outbound = OutboundOperation::factory()->create();

        $items = [
            ['product_id' => $product->id, 'shipped_quantity' => 10],
        ];

        $this->service->recordOutbound($outbound, $items);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => -10,
            'type' => StockMovementType::OUTBOUND->value,
            'reference_type' => OutboundOperation::class,
            'reference_id' => $outbound->id,
        ]);

        $this->assertEquals(40, $this->service->getCurrentStock($product));
    }

    /** @test */
    public function it_throws_exception_for_insufficient_stock_on_outbound()
    {
        $product = Product::factory()->create();
        
        // Create initial stock of 5
        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 5,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        Cache::forget("product_stock_{$product->id}");

        $outbound = OutboundOperation::factory()->create();

        $items = [
            ['product_id' => $product->id, 'shipped_quantity' => 10],
        ];

        $this->expectException(InsufficientStockException::class);

        $this->service->recordOutbound($outbound, $items);
    }

    /** @test */
    public function it_throws_exception_for_empty_outbound_items()
    {
        $outbound = OutboundOperation::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Items array cannot be empty');

        $this->service->recordOutbound($outbound, []);
    }

    /** @test */
    public function it_records_positive_adjustment_correctly()
    {
        $product = Product::factory()->create();
        $opname = StockOpname::factory()->create();

        // Initial stock is 10
        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 10,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        Cache::forget("product_stock_{$product->id}");

        // Physical count shows 15 (surplus of 5)
        $variance = 5;

        $this->service->recordAdjustment($opname, $product, $variance);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => 5,
            'type' => StockMovementType::ADJUSTMENT_PLUS->value,
            'reference_type' => StockOpname::class,
            'reference_id' => $opname->id,
        ]);

        $this->assertEquals(15, $this->service->getCurrentStock($product));
    }

    /** @test */
    public function it_records_negative_adjustment_correctly()
    {
        $product = Product::factory()->create();
        $opname = StockOpname::factory()->create();

        // Initial stock is 10
        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 10,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        Cache::forget("product_stock_{$product->id}");

        // Physical count shows 7 (shortage of 3)
        $variance = -3;

        $this->service->recordAdjustment($opname, $product, $variance);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => -3,
            'type' => StockMovementType::ADJUSTMENT_MINUS->value,
            'reference_type' => StockOpname::class,
            'reference_id' => $opname->id,
        ]);

        $this->assertEquals(7, $this->service->getCurrentStock($product));
    }

    /** @test */
    public function it_does_not_record_adjustment_for_zero_variance()
    {
        $product = Product::factory()->create();
        $opname = StockOpname::factory()->create();

        $initialCount = StockMovement::count();

        $this->service->recordAdjustment($opname, $product, 0);

        $this->assertEquals($initialCount, StockMovement::count());
    }

    /** @test */
    public function it_calculates_current_stock_from_movements()
    {
        $product = Product::factory()->create();

        // Create multiple movements
        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 100,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => -30,
            'type' => StockMovementType::OUTBOUND,
            'created_by' => $this->user->id,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 50,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => -20,
            'type' => StockMovementType::OUTBOUND,
            'created_by' => $this->user->id,
        ]);

        Cache::forget("product_stock_{$product->id}");

        // 100 - 30 + 50 - 20 = 100
        $this->assertEquals(100, $this->service->getCurrentStock($product));
    }

    /** @test */
    public function it_checks_availability_correctly()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Product 1 has stock of 50
        StockMovement::create([
            'product_id' => $product1->id,
            'quantity' => 50,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        // Product 2 has stock of 10
        StockMovement::create([
            'product_id' => $product2->id,
            'quantity' => 10,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        Cache::forget("product_stock_{$product1->id}");
        Cache::forget("product_stock_{$product2->id}");

        $items = [
            ['product_id' => $product1->id, 'quantity' => 30], // Available
            ['product_id' => $product2->id, 'quantity' => 20], // Not available
        ];

        $unavailable = $this->service->checkAvailability($items);

        $this->assertCount(1, $unavailable);
        $this->assertEquals($product2->id, $unavailable[0]['product_id']);
        $this->assertEquals(20, $unavailable[0]['required']);
        $this->assertEquals(10, $unavailable[0]['available']);
        $this->assertEquals(10, $unavailable[0]['shortage']);
    }

    /** @test */
    public function it_returns_empty_array_when_all_items_available()
    {
        $product = Product::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 100,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        Cache::forget("product_stock_{$product->id}");

        $items = [
            ['product_id' => $product->id, 'quantity' => 50],
        ];

        $unavailable = $this->service->checkAvailability($items);

        $this->assertEmpty($unavailable);
    }

    /** @test */
    public function it_invalidates_cache_after_recording_movement()
    {
        $product = Product::factory()->create();
        $inbound = InboundOperation::factory()->create();

        // First call to populate cache
        $this->service->getCurrentStock($product);
        $this->assertTrue(Cache::has("product_stock_{$product->id}"));

        // Record movement should invalidate cache
        $items = [
            ['product_id' => $product->id, 'received_quantity' => 10],
        ];

        $this->service->recordInbound($inbound, $items);

        $this->assertFalse(Cache::has("product_stock_{$product->id}"));
    }
}
