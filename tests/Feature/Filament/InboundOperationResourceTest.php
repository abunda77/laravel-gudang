<?php

namespace Tests\Feature\Filament;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\InboundOperation;
use App\Models\InboundOperationItem;
use App\Enums\PurchaseOrderStatus;
use App\Services\StockMovementService;
use App\Filament\Resources\InboundOperationResource;
use App\Filament\Resources\InboundOperationResource\Pages\ListInboundOperations;
use App\Filament\Resources\InboundOperationResource\Pages\CreateInboundOperation;
use App\Filament\Resources\InboundOperationResource\Pages\EditInboundOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class InboundOperationResourceTest extends TestCase
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

    public function test_can_render_inbound_operation_list_page(): void
    {
        $this->get(InboundOperationResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_list_inbound_operations(): void
    {
        $inboundOperations = InboundOperation::factory()->count(10)->create();

        Livewire::test(ListInboundOperations::class)
            ->assertCanSeeTableRecords($inboundOperations);
    }

    public function test_can_render_inbound_operation_create_page(): void
    {
        $this->get(InboundOperationResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_can_create_inbound_operation(): void
    {
        $supplier = Supplier::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::SENT,
        ]);

        $newData = [
            'purchase_order_id' => $purchaseOrder->id,
            'received_date' => now()->format('Y-m-d H:i:s'),
            'received_by' => $this->user->id,
            'notes' => 'Test inbound operation',
            'items' => [
                [
                    'product_id' => $product1->id,
                    'ordered_quantity' => 100,
                    'received_quantity' => 100,
                ],
                [
                    'product_id' => $product2->id,
                    'ordered_quantity' => 50,
                    'received_quantity' => 45,
                ],
            ],
        ];

        Livewire::test(CreateInboundOperation::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('inbound_operations', [
            'purchase_order_id' => $purchaseOrder->id,
            'received_by' => $this->user->id,
        ]);

        $inboundOperation = InboundOperation::where('purchase_order_id', $purchaseOrder->id)->first();
        $this->assertNotNull($inboundOperation);
        $this->assertNotNull($inboundOperation->inbound_number);
        $this->assertStringStartsWith('IN-', $inboundOperation->inbound_number);
        $this->assertEquals(2, $inboundOperation->items()->count());
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
    }

    public function test_can_validate_inbound_operation_input(): void
    {
        Livewire::test(CreateInboundOperation::class)
            ->fillForm([
                'purchase_order_id' => null,
                'received_date' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['purchase_order_id', 'received_date']);
    }

    public function test_can_render_inbound_operation_edit_page(): void
    {
        $inboundOperation = InboundOperation::factory()->create();

        $this->get(InboundOperationResource::getUrl('edit', ['record' => $inboundOperation]))
            ->assertSuccessful();
    }

    public function test_can_retrieve_inbound_operation_data_in_edit_page(): void
    {
        $inboundOperation = InboundOperation::factory()->create();

        Livewire::test(EditInboundOperation::class, ['record' => $inboundOperation->getRouteKey()])
            ->assertFormSet([
                'purchase_order_id' => $inboundOperation->purchase_order_id,
                'received_by' => $inboundOperation->received_by,
            ]);
    }

    public function test_can_update_inbound_operation(): void
    {
        $inboundOperation = InboundOperation::factory()->create();

        $newData = [
            'notes' => 'Updated notes',
        ];

        Livewire::test(EditInboundOperation::class, ['record' => $inboundOperation->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('inbound_operations', [
            'id' => $inboundOperation->id,
            'notes' => 'Updated notes',
        ]);
    }

    public function test_can_search_inbound_operations_by_inbound_number(): void
    {
        $inboundOperations = InboundOperation::factory()->count(5)->create();
        $searchOperation = $inboundOperations->first();

        Livewire::test(ListInboundOperations::class)
            ->searchTable($searchOperation->inbound_number)
            ->assertCanSeeTableRecords([$searchOperation])
            ->assertCanNotSeeTableRecords($inboundOperations->skip(1));
    }

    public function test_inbound_operation_processing_increases_stock(): void
    {
        $supplier = Supplier::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Verify initial stock is zero
        $this->assertEquals(0, $this->stockService->getCurrentStock($product1));
        $this->assertEquals(0, $this->stockService->getCurrentStock($product2));

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::SENT,
        ]);

        $inboundOperation = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'received_by' => $this->user->id,
        ]);

        $items = [
            [
                'product_id' => $product1->id,
                'ordered_quantity' => 100,
                'received_quantity' => 100,
            ],
            [
                'product_id' => $product2->id,
                'ordered_quantity' => 50,
                'received_quantity' => 45,
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

        // Process inbound operation
        $this->stockService->recordInbound($inboundOperation, $items);

        // Verify stock increased
        $this->assertEquals(100, $this->stockService->getCurrentStock($product1));
        $this->assertEquals(45, $this->stockService->getCurrentStock($product2));
    }

    public function test_inbound_operation_with_partial_receipt(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::SENT,
        ]);

        $inboundOperation = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        // Ordered 100 but received only 80
        $items = [
            [
                'product_id' => $product->id,
                'ordered_quantity' => 100,
                'received_quantity' => 80,
            ],
        ];

        InboundOperationItem::factory()->create([
            'inbound_operation_id' => $inboundOperation->id,
            'product_id' => $product->id,
            'ordered_quantity' => 100,
            'received_quantity' => 80,
        ]);

        $this->stockService->recordInbound($inboundOperation, $items);

        // Stock should reflect received quantity, not ordered
        $this->assertEquals(80, $this->stockService->getCurrentStock($product));
    }

    public function test_can_filter_inbound_operations_by_purchase_order(): void
    {
        $supplier = Supplier::factory()->create();
        $po1 = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);
        $po2 = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);

        $inboundsPo1 = InboundOperation::factory()->count(3)->create(['purchase_order_id' => $po1->id]);
        $inboundsPo2 = InboundOperation::factory()->count(2)->create(['purchase_order_id' => $po2->id]);

        Livewire::test(ListInboundOperations::class)
            ->filterTable('purchase_order', $po1->id)
            ->assertCanSeeTableRecords($inboundsPo1)
            ->assertCanNotSeeTableRecords($inboundsPo2);
    }

    public function test_inbound_operation_links_to_purchase_order(): void
    {
        $supplier = Supplier::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        $inboundOperation = InboundOperation::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        // Verify relationship
        $this->assertEquals($purchaseOrder->id, $inboundOperation->purchaseOrder->id);
        $this->assertTrue($purchaseOrder->inboundOperations->contains($inboundOperation));
    }
}
