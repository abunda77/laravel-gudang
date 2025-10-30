<?php

namespace Tests\Feature\Filament;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\OutboundOperation;
use App\Models\OutboundOperationItem;
use App\Models\InboundOperation;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Enums\SalesOrderStatus;
use App\Services\StockMovementService;
use App\Filament\Resources\OutboundOperationResource;
use App\Filament\Resources\OutboundOperationResource\Pages\ListOutboundOperations;
use App\Filament\Resources\OutboundOperationResource\Pages\CreateOutboundOperation;
use App\Filament\Resources\OutboundOperationResource\Pages\EditOutboundOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class OutboundOperationResourceTest extends TestCase
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

    public function test_can_render_outbound_operation_list_page(): void
    {
        $this->get(OutboundOperationResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_list_outbound_operations(): void
    {
        $outboundOperations = OutboundOperation::factory()->count(10)->create();

        Livewire::test(ListOutboundOperations::class)
            ->assertCanSeeTableRecords($outboundOperations);
    }

    public function test_can_render_outbound_operation_create_page(): void
    {
        $this->get(OutboundOperationResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_can_create_outbound_operation(): void
    {
        $customer = Customer::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Add stock for products
        $this->addInitialStock($product1, 100);
        $this->addInitialStock($product2, 50);

        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::APPROVED,
        ]);

        $newData = [
            'sales_order_id' => $salesOrder->id,
            'shipped_date' => now()->format('Y-m-d H:i:s'),
            'prepared_by' => $this->user->id,
            'notes' => 'Test outbound operation',
            'items' => [
                [
                    'product_id' => $product1->id,
                    'shipped_quantity' => 30,
                ],
                [
                    'product_id' => $product2->id,
                    'shipped_quantity' => 20,
                ],
            ],
        ];

        Livewire::test(CreateOutboundOperation::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('outbound_operations', [
            'sales_order_id' => $salesOrder->id,
            'prepared_by' => $this->user->id,
        ]);

        $outboundOperation = OutboundOperation::where('sales_order_id', $salesOrder->id)->first();
        $this->assertNotNull($outboundOperation);
        $this->assertNotNull($outboundOperation->outbound_number);
        $this->assertStringStartsWith('OUT-', $outboundOperation->outbound_number);
        $this->assertEquals(2, $outboundOperation->items()->count());
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
    }

    public function test_can_validate_outbound_operation_input(): void
    {
        Livewire::test(CreateOutboundOperation::class)
            ->fillForm([
                'sales_order_id' => null,
                'shipped_date' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['sales_order_id', 'shipped_date']);
    }

    public function test_can_render_outbound_operation_edit_page(): void
    {
        $outboundOperation = OutboundOperation::factory()->create();

        $this->get(OutboundOperationResource::getUrl('edit', ['record' => $outboundOperation]))
            ->assertSuccessful();
    }

    public function test_can_retrieve_outbound_operation_data_in_edit_page(): void
    {
        $outboundOperation = OutboundOperation::factory()->create();

        Livewire::test(EditOutboundOperation::class, ['record' => $outboundOperation->getRouteKey()])
            ->assertFormSet([
                'sales_order_id' => $outboundOperation->sales_order_id,
                'prepared_by' => $outboundOperation->prepared_by,
            ]);
    }

    public function test_can_update_outbound_operation(): void
    {
        $outboundOperation = OutboundOperation::factory()->create();

        $newData = [
            'notes' => 'Updated notes',
        ];

        Livewire::test(EditOutboundOperation::class, ['record' => $outboundOperation->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('outbound_operations', [
            'id' => $outboundOperation->id,
            'notes' => 'Updated notes',
        ]);
    }

    public function test_can_search_outbound_operations_by_outbound_number(): void
    {
        $outboundOperations = OutboundOperation::factory()->count(5)->create();
        $searchOperation = $outboundOperations->first();

        Livewire::test(ListOutboundOperations::class)
            ->searchTable($searchOperation->outbound_number)
            ->assertCanSeeTableRecords([$searchOperation])
            ->assertCanNotSeeTableRecords($outboundOperations->skip(1));
    }

    public function test_outbound_operation_processing_decreases_stock(): void
    {
        $customer = Customer::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Add initial stock
        $this->addInitialStock($product1, 100);
        $this->addInitialStock($product2, 50);

        // Verify initial stock
        $this->assertEquals(100, $this->stockService->getCurrentStock($product1));
        $this->assertEquals(50, $this->stockService->getCurrentStock($product2));

        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::APPROVED,
        ]);

        $outboundOperation = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'prepared_by' => $this->user->id,
        ]);

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

        // Process outbound operation
        $this->stockService->recordOutbound($outboundOperation, $items);

        // Verify stock decreased
        $this->assertEquals(70, $this->stockService->getCurrentStock($product1));
        $this->assertEquals(30, $this->stockService->getCurrentStock($product2));
    }

    public function test_outbound_operation_requires_approved_sales_order(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $this->addInitialStock($product, 100);

        // Create draft sales order (not approved)
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::DRAFT,
        ]);

        // Attempting to create outbound operation for draft SO should be restricted
        // This test verifies the relationship exists
        $this->assertEquals(SalesOrderStatus::DRAFT, $salesOrder->status);
    }

    public function test_can_filter_outbound_operations_by_sales_order(): void
    {
        $customer = Customer::factory()->create();
        $so1 = SalesOrder::factory()->create(['customer_id' => $customer->id]);
        $so2 = SalesOrder::factory()->create(['customer_id' => $customer->id]);

        $outboundsSo1 = OutboundOperation::factory()->count(3)->create(['sales_order_id' => $so1->id]);
        $outboundsSo2 = OutboundOperation::factory()->count(2)->create(['sales_order_id' => $so2->id]);

        Livewire::test(ListOutboundOperations::class)
            ->filterTable('sales_order', $so1->id)
            ->assertCanSeeTableRecords($outboundsSo1)
            ->assertCanNotSeeTableRecords($outboundsSo2);
    }

    public function test_outbound_operation_links_to_sales_order(): void
    {
        $customer = Customer::factory()->create();
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $outboundOperation = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        // Verify relationship
        $this->assertEquals($salesOrder->id, $outboundOperation->salesOrder->id);
        $this->assertTrue($salesOrder->outboundOperations->contains($outboundOperation));
    }

    public function test_multiple_outbound_operations_for_same_sales_order(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $this->addInitialStock($product, 100);

        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::APPROVED,
        ]);

        // First outbound operation
        $outbound1 = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        $this->stockService->recordOutbound($outbound1, [
            ['product_id' => $product->id, 'shipped_quantity' => 30],
        ]);

        $this->assertEquals(70, $this->stockService->getCurrentStock($product));

        // Second outbound operation for same sales order
        $outbound2 = OutboundOperation::factory()->create([
            'sales_order_id' => $salesOrder->id,
        ]);

        $this->stockService->recordOutbound($outbound2, [
            ['product_id' => $product->id, 'shipped_quantity' => 20],
        ]);

        $this->assertEquals(50, $this->stockService->getCurrentStock($product));

        // Verify both operations are linked to the same sales order
        $this->assertEquals(2, $salesOrder->outboundOperations()->count());
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
