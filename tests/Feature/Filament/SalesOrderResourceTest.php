<?php

namespace Tests\Feature\Filament;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\InboundOperation;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Enums\SalesOrderStatus;
use App\Services\StockMovementService;
use App\Filament\Resources\SalesOrderResource;
use App\Filament\Resources\SalesOrderResource\Pages\ListSalesOrders;
use App\Filament\Resources\SalesOrderResource\Pages\CreateSalesOrder;
use App\Filament\Resources\SalesOrderResource\Pages\EditSalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class SalesOrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected StockMovementService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run permission seeder
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        $this->user = User::factory()->create();
        $this->user->assignRole('super_admin');
        $this->actingAs($this->user);
        $this->stockService = app(StockMovementService::class);
    }

    public function test_can_render_sales_order_list_page(): void
    {
        $this->get(SalesOrderResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_list_sales_orders(): void
    {
        $salesOrders = SalesOrder::factory()->count(10)->create();

        Livewire::test(ListSalesOrders::class)
            ->assertCanSeeTableRecords($salesOrders);
    }

    public function test_can_render_sales_order_create_page(): void
    {
        $this->get(SalesOrderResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_can_create_sales_order(): void
    {
        $customer = Customer::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Add stock for products
        $this->addInitialStock($product1, 100);
        $this->addInitialStock($product2, 50);

        $newData = [
            'customer_id' => $customer->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => SalesOrderStatus::DRAFT->value,
            'notes' => 'Test sales order',
            'sales_user_id' => $this->user->id,
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 10,
                    'unit_price' => 75.00,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 5,
                    'unit_price' => 100.00,
                ],
            ],
        ];

        Livewire::test(CreateSalesOrder::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sales_orders', [
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::DRAFT->value,
        ]);

        $salesOrder = SalesOrder::where('customer_id', $customer->id)->first();
        $this->assertNotNull($salesOrder);
        $this->assertNotNull($salesOrder->so_number);
        $this->assertStringStartsWith('SO-', $salesOrder->so_number);
        $this->assertEquals(2, $salesOrder->items()->count());
    }

    public function test_sales_order_generates_unique_number(): void
    {
        $customer = Customer::factory()->create();

        $so1 = SalesOrder::factory()->create(['customer_id' => $customer->id]);
        $so2 = SalesOrder::factory()->create(['customer_id' => $customer->id]);

        $this->assertNotNull($so1->so_number);
        $this->assertNotNull($so2->so_number);
        $this->assertNotEquals($so1->so_number, $so2->so_number);
    }

    public function test_can_validate_sales_order_input(): void
    {
        Livewire::test(CreateSalesOrder::class)
            ->fillForm([
                'customer_id' => null,
                'order_date' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['customer_id', 'order_date']);
    }

    public function test_can_render_sales_order_edit_page(): void
    {
        $salesOrder = SalesOrder::factory()->create();

        $this->get(SalesOrderResource::getUrl('edit', ['record' => $salesOrder]))
            ->assertSuccessful();
    }

    public function test_can_retrieve_sales_order_data_in_edit_page(): void
    {
        $salesOrder = SalesOrder::factory()->create();

        Livewire::test(EditSalesOrder::class, ['record' => $salesOrder->getRouteKey()])
            ->assertFormSet([
                'customer_id' => $salesOrder->customer_id,
                'status' => $salesOrder->status->value,
            ]);
    }

    public function test_can_update_sales_order(): void
    {
        $salesOrder = SalesOrder::factory()->create([
            'status' => SalesOrderStatus::DRAFT,
        ]);

        $newData = [
            'status' => SalesOrderStatus::APPROVED->value,
            'notes' => 'Updated notes',
        ];

        Livewire::test(EditSalesOrder::class, ['record' => $salesOrder->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'status' => SalesOrderStatus::APPROVED->value,
            'notes' => 'Updated notes',
        ]);
    }

    public function test_can_filter_sales_orders_by_status(): void
    {
        $draftOrders = SalesOrder::factory()->count(3)->create([
            'status' => SalesOrderStatus::DRAFT,
        ]);
        $approvedOrders = SalesOrder::factory()->count(2)->create([
            'status' => SalesOrderStatus::APPROVED,
        ]);

        Livewire::test(ListSalesOrders::class)
            ->filterTable('status', SalesOrderStatus::DRAFT->value)
            ->assertCanSeeTableRecords($draftOrders)
            ->assertCanNotSeeTableRecords($approvedOrders);
    }

    public function test_can_filter_sales_orders_by_customer(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();

        $ordersCustomer1 = SalesOrder::factory()->count(3)->create(['customer_id' => $customer1->id]);
        $ordersCustomer2 = SalesOrder::factory()->count(2)->create(['customer_id' => $customer2->id]);

        Livewire::test(ListSalesOrders::class)
            ->filterTable('customer', $customer1->id)
            ->assertCanSeeTableRecords($ordersCustomer1)
            ->assertCanNotSeeTableRecords($ordersCustomer2);
    }

    public function test_can_search_sales_orders_by_so_number(): void
    {
        $salesOrders = SalesOrder::factory()->count(5)->create();
        $searchOrder = $salesOrders->first();

        Livewire::test(ListSalesOrders::class)
            ->searchTable($searchOrder->so_number)
            ->assertCanSeeTableRecords([$searchOrder])
            ->assertCanNotSeeTableRecords($salesOrders->skip(1));
    }

    public function test_sales_order_with_stock_validation(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        // Add limited stock
        $this->addInitialStock($product, 10);

        // Create sales order with quantity within stock
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::DRAFT,
        ]);

        SalesOrderItem::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $product->id,
            'quantity' => 5, // Within available stock
            'unit_price' => 100.00,
        ]);

        // Verify stock availability
        $unavailable = $this->stockService->checkAvailability([
            ['product_id' => $product->id, 'quantity' => 5],
        ]);

        $this->assertEmpty($unavailable);
    }

    public function test_sales_order_detects_insufficient_stock(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        // Add limited stock
        $this->addInitialStock($product, 10);

        // Create sales order with quantity exceeding stock
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::DRAFT,
        ]);

        SalesOrderItem::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $product->id,
            'quantity' => 20, // Exceeds available stock
            'unit_price' => 100.00,
        ]);

        // Verify stock availability check fails
        $unavailable = $this->stockService->checkAvailability([
            ['product_id' => $product->id, 'quantity' => 20],
        ]);

        $this->assertNotEmpty($unavailable);
        $this->assertEquals($product->id, $unavailable[0]['product_id']);
        $this->assertEquals(20, $unavailable[0]['required']);
        $this->assertEquals(10, $unavailable[0]['available']);
    }

    public function test_sales_order_workflow_from_draft_to_approved(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        // Add sufficient stock
        $this->addInitialStock($product, 100);

        // Create draft sales order
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::DRAFT,
        ]);

        SalesOrderItem::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        $this->assertEquals(SalesOrderStatus::DRAFT, $salesOrder->status);

        // Update status to approved
        Livewire::test(EditSalesOrder::class, ['record' => $salesOrder->getRouteKey()])
            ->fillForm(['status' => SalesOrderStatus::APPROVED->value])
            ->call('save')
            ->assertHasNoFormErrors();

        $salesOrder->refresh();
        $this->assertEquals(SalesOrderStatus::APPROVED, $salesOrder->status);
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
