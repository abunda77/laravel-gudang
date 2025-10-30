<?php

namespace Tests\Unit\Services;

use App\Enums\StockMovementType;
use App\Models\Customer;
use App\Models\InboundOperation;
use App\Models\InboundOperationItem;
use App\Models\OutboundOperation;
use App\Models\OutboundOperationItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReportService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ReportService();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_generates_stock_card_with_running_balance()
    {
        $product = Product::factory()->create();

        // Create stock movements
        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 100,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
            'created_at' => now()->subDays(3),
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => -30,
            'type' => StockMovementType::OUTBOUND,
            'created_by' => $this->user->id,
            'created_at' => now()->subDays(2),
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 50,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
            'created_at' => now()->subDays(1),
        ]);

        $stockCard = $this->service->getStockCard($product);

        $this->assertCount(3, $stockCard);
        
        // Verify running balance calculation
        $this->assertEquals(100, $stockCard[0]->running_balance);
        $this->assertEquals(70, $stockCard[1]->running_balance);
        $this->assertEquals(120, $stockCard[2]->running_balance);
    }

    /** @test */
    public function it_filters_stock_card_by_date_range()
    {
        $product = Product::factory()->create();

        $movement1 = StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 100,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);
        $movement1->created_at = now()->subDays(10);
        $movement1->save();

        $movement2 = StockMovement::create([
            'product_id' => $product->id,
            'quantity' => -30,
            'type' => StockMovementType::OUTBOUND,
            'created_by' => $this->user->id,
        ]);
        $movement2->created_at = now()->subDays(5);
        $movement2->save();

        $movement3 = StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 50,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);
        $movement3->created_at = now()->subDays(2);
        $movement3->save();

        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->subDays(1)->endOfDay();

        $stockCard = $this->service->getStockCard($product, $startDate, $endDate);

        $this->assertCount(2, $stockCard);
    }

    /** @test */
    public function it_calculates_opening_balance_for_date_range()
    {
        $product = Product::factory()->create();

        // Movement before start date
        $movement1 = StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 100,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);
        $movement1->created_at = now()->subDays(10);
        $movement1->save();

        // Movement within date range
        $movement2 = StockMovement::create([
            'product_id' => $product->id,
            'quantity' => 50,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);
        $movement2->created_at = now()->subDays(2);
        $movement2->save();

        $startDate = now()->subDays(5)->startOfDay();

        $stockCard = $this->service->getStockCard($product, $startDate);

        $this->assertCount(1, $stockCard);
        // Opening balance is 100, plus 50 = 150
        $this->assertEquals(150, $stockCard[0]->running_balance);
    }

    /** @test */
    public function it_gets_low_stock_products()
    {
        $product1 = Product::factory()->create(['minimum_stock' => 50]);
        $product2 = Product::factory()->create(['minimum_stock' => 20]);
        $product3 = Product::factory()->create(['minimum_stock' => 30]);

        // Product 1: stock = 30 (below minimum of 50)
        StockMovement::create([
            'product_id' => $product1->id,
            'quantity' => 30,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        // Product 2: stock = 25 (above minimum of 20)
        StockMovement::create([
            'product_id' => $product2->id,
            'quantity' => 25,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        // Product 3: stock = 10 (below minimum of 30)
        StockMovement::create([
            'product_id' => $product3->id,
            'quantity' => 10,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        Cache::flush();

        $lowStockProducts = $this->service->getLowStockProducts();

        $this->assertCount(2, $lowStockProducts);
        
        $productIds = $lowStockProducts->pluck('id')->toArray();
        $this->assertContains($product1->id, $productIds);
        $this->assertContains($product3->id, $productIds);
        $this->assertNotContains($product2->id, $productIds);
    }

    /** @test */
    public function it_generates_stock_valuation_report()
    {
        $category = ProductCategory::factory()->create();
        
        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'purchase_price' => 100,
        ]);
        
        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'purchase_price' => 200,
        ]);

        // Product 1: stock = 10, value = 1000
        StockMovement::create([
            'product_id' => $product1->id,
            'quantity' => 10,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        // Product 2: stock = 5, value = 1000
        StockMovement::create([
            'product_id' => $product2->id,
            'quantity' => 5,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        Cache::flush();

        $report = $this->service->getStockValuationReport();

        $this->assertEquals(2000, $report['total_value']);
        $this->assertEquals(2, $report['total_products']);
        $this->assertCount(2, $report['items']);
    }

    /** @test */
    public function it_filters_stock_valuation_by_category()
    {
        $category1 = ProductCategory::factory()->create();
        $category2 = ProductCategory::factory()->create();
        
        $product1 = Product::factory()->create([
            'category_id' => $category1->id,
            'purchase_price' => 100,
        ]);
        
        $product2 = Product::factory()->create([
            'category_id' => $category2->id,
            'purchase_price' => 200,
        ]);

        StockMovement::create([
            'product_id' => $product1->id,
            'quantity' => 10,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        StockMovement::create([
            'product_id' => $product2->id,
            'quantity' => 5,
            'type' => StockMovementType::INBOUND,
            'created_by' => $this->user->id,
        ]);

        Cache::flush();

        $report = $this->service->getStockValuationReport($category1->id);

        $this->assertEquals(1000, $report['total_value']);
        $this->assertEquals(1, $report['total_products']);
    }

    /** @test */
    public function it_generates_sales_report()
    {
        $customer = Customer::factory()->create();
        $salesUser = User::factory()->create();
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'sales_user_id' => $salesUser->id,
        ]);

        $product = Product::factory()->create(['selling_price' => 100]);

        $outbound = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'shipped_date' => now(),
        ]);

        OutboundOperationItem::factory()->create([
            'outbound_operation_id' => $outbound->id,
            'product_id' => $product->id,
            'shipped_quantity' => 10,
        ]);

        $report = $this->service->getSalesReport();

        $this->assertEquals(10, $report['total_quantity']);
        $this->assertEquals(1000, $report['total_value']);
        $this->assertEquals(1, $report['total_transactions']);
        $this->assertCount(1, $report['items']);
    }

    /** @test */
    public function it_filters_sales_report_by_date_range()
    {
        $customer = Customer::factory()->create();
        $salesOrder = SalesOrder::factory()->create(['customer_id' => $customer->id]);
        $product = Product::factory()->create(['selling_price' => 100]);

        // Outbound within date range
        $outbound1 = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'shipped_date' => now()->subDays(2),
        ]);

        OutboundOperationItem::factory()->create([
            'outbound_operation_id' => $outbound1->id,
            'product_id' => $product->id,
            'shipped_quantity' => 10,
        ]);

        // Outbound outside date range
        $outbound2 = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'shipped_date' => now()->subDays(10),
        ]);

        OutboundOperationItem::factory()->create([
            'outbound_operation_id' => $outbound2->id,
            'product_id' => $product->id,
            'shipped_quantity' => 5,
        ]);

        $filters = [
            'start_date' => now()->subDays(5),
            'end_date' => now(),
        ];

        $report = $this->service->getSalesReport($filters);

        $this->assertEquals(1, $report['total_transactions']);
        $this->assertEquals(10, $report['total_quantity']);
    }

    /** @test */
    public function it_filters_sales_report_by_customer()
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        
        $salesOrder1 = SalesOrder::factory()->create(['customer_id' => $customer1->id]);
        $salesOrder2 = SalesOrder::factory()->create(['customer_id' => $customer2->id]);
        
        $product = Product::factory()->create(['selling_price' => 100]);

        $outbound1 = OutboundOperation::factory()->create(['sales_order_id' => $salesOrder1->id]);
        OutboundOperationItem::factory()->create([
            'outbound_operation_id' => $outbound1->id,
            'product_id' => $product->id,
            'shipped_quantity' => 10,
        ]);

        $outbound2 = OutboundOperation::factory()->create(['sales_order_id' => $salesOrder2->id]);
        OutboundOperationItem::factory()->create([
            'outbound_operation_id' => $outbound2->id,
            'product_id' => $product->id,
            'shipped_quantity' => 5,
        ]);

        $filters = ['customer_id' => $customer1->id];

        $report = $this->service->getSalesReport($filters);

        $this->assertEquals(1, $report['total_transactions']);
        $this->assertEquals(10, $report['total_quantity']);
    }

    /** @test */
    public function it_generates_purchase_report()
    {
        $supplier = Supplier::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);
        $product = Product::factory()->create(['purchase_price' => 50]);

        $inbound = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'received_date' => now(),
        ]);

        InboundOperationItem::factory()->create([
            'inbound_operation_id' => $inbound->id,
            'product_id' => $product->id,
            'ordered_quantity' => 20,
            'received_quantity' => 20,
        ]);

        $report = $this->service->getPurchaseReport();

        $this->assertEquals(20, $report['total_quantity']);
        $this->assertEquals(1000, $report['total_value']);
        $this->assertEquals(1, $report['total_transactions']);
        $this->assertCount(1, $report['items']);
    }

    /** @test */
    public function it_filters_purchase_report_by_date_range()
    {
        $supplier = Supplier::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);
        $product = Product::factory()->create(['purchase_price' => 50]);

        // Inbound within date range
        $inbound1 = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'received_date' => now()->subDays(2),
        ]);

        InboundOperationItem::factory()->create([
            'inbound_operation_id' => $inbound1->id,
            'product_id' => $product->id,
            'ordered_quantity' => 20,
            'received_quantity' => 20,
        ]);

        // Inbound outside date range
        $inbound2 = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'received_date' => now()->subDays(10),
        ]);

        InboundOperationItem::factory()->create([
            'inbound_operation_id' => $inbound2->id,
            'product_id' => $product->id,
            'ordered_quantity' => 10,
            'received_quantity' => 10,
        ]);

        $filters = [
            'start_date' => now()->subDays(5),
            'end_date' => now(),
        ];

        $report = $this->service->getPurchaseReport($filters);

        $this->assertEquals(1, $report['total_transactions']);
        $this->assertEquals(20, $report['total_quantity']);
    }

    /** @test */
    public function it_filters_purchase_report_by_supplier()
    {
        $supplier1 = Supplier::factory()->create();
        $supplier2 = Supplier::factory()->create();
        
        $purchaseOrder1 = PurchaseOrder::factory()->create(['supplier_id' => $supplier1->id]);
        $purchaseOrder2 = PurchaseOrder::factory()->create(['supplier_id' => $supplier2->id]);
        
        $product = Product::factory()->create(['purchase_price' => 50]);

        $inbound1 = InboundOperation::factory()->create(['purchase_order_id' => $purchaseOrder1->id]);
        InboundOperationItem::factory()->create([
            'inbound_operation_id' => $inbound1->id,
            'product_id' => $product->id,
            'ordered_quantity' => 20,
            'received_quantity' => 20,
        ]);

        $inbound2 = InboundOperation::factory()->create(['purchase_order_id' => $purchaseOrder2->id]);
        InboundOperationItem::factory()->create([
            'inbound_operation_id' => $inbound2->id,
            'product_id' => $product->id,
            'ordered_quantity' => 10,
            'received_quantity' => 10,
        ]);

        $filters = ['supplier_id' => $supplier1->id];

        $report = $this->service->getPurchaseReport($filters);

        $this->assertEquals(1, $report['total_transactions']);
        $this->assertEquals(20, $report['total_quantity']);
    }
}
