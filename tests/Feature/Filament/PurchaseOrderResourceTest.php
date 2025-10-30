<?php

namespace Tests\Feature\Filament;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Enums\PurchaseOrderStatus;
use App\Filament\Resources\PurchaseOrderResource;
use App\Filament\Resources\PurchaseOrderResource\Pages\ListPurchaseOrders;
use App\Filament\Resources\PurchaseOrderResource\Pages\CreatePurchaseOrder;
use App\Filament\Resources\PurchaseOrderResource\Pages\EditPurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class PurchaseOrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run permission seeder
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        $this->user = User::factory()->create();
        $this->user->assignRole('super_admin');
        $this->actingAs($this->user);
    }

    public function test_can_render_purchase_order_list_page(): void
    {
        $this->get(PurchaseOrderResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_list_purchase_orders(): void
    {
        $purchaseOrders = PurchaseOrder::factory()->count(10)->create();

        Livewire::test(ListPurchaseOrders::class)
            ->assertCanSeeTableRecords($purchaseOrders);
    }

    public function test_can_render_purchase_order_create_page(): void
    {
        $this->get(PurchaseOrderResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_can_create_purchase_order(): void
    {
        $supplier = Supplier::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $newData = [
            'supplier_id' => $supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'expected_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => PurchaseOrderStatus::DRAFT->value,
            'notes' => 'Test purchase order',
            'items' => [
                [
                    'product_id' => $product1->id,
                    'ordered_quantity' => 100,
                    'unit_price' => 50.00,
                ],
                [
                    'product_id' => $product2->id,
                    'ordered_quantity' => 50,
                    'unit_price' => 75.00,
                ],
            ],
        ];

        Livewire::test(CreatePurchaseOrder::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::DRAFT->value,
        ]);

        $purchaseOrder = PurchaseOrder::where('supplier_id', $supplier->id)->first();
        $this->assertNotNull($purchaseOrder);
        $this->assertNotNull($purchaseOrder->po_number);
        $this->assertStringStartsWith('PO-', $purchaseOrder->po_number);
        $this->assertEquals(2, $purchaseOrder->items()->count());
    }

    public function test_purchase_order_generates_unique_number(): void
    {
        $supplier = Supplier::factory()->create();

        $po1 = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);
        $po2 = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);

        $this->assertNotNull($po1->po_number);
        $this->assertNotNull($po2->po_number);
        $this->assertNotEquals($po1->po_number, $po2->po_number);
    }

    public function test_can_validate_purchase_order_input(): void
    {
        Livewire::test(CreatePurchaseOrder::class)
            ->fillForm([
                'supplier_id' => null,
                'order_date' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['supplier_id', 'order_date']);
    }

    public function test_can_render_purchase_order_edit_page(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create();

        $this->get(PurchaseOrderResource::getUrl('edit', ['record' => $purchaseOrder]))
            ->assertSuccessful();
    }

    public function test_can_retrieve_purchase_order_data_in_edit_page(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create();

        Livewire::test(EditPurchaseOrder::class, ['record' => $purchaseOrder->getRouteKey()])
            ->assertFormSet([
                'supplier_id' => $purchaseOrder->supplier_id,
                'status' => $purchaseOrder->status->value,
            ]);
    }

    public function test_can_update_purchase_order(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create([
            'status' => PurchaseOrderStatus::DRAFT,
        ]);

        $newData = [
            'status' => PurchaseOrderStatus::SENT->value,
            'notes' => 'Updated notes',
        ];

        Livewire::test(EditPurchaseOrder::class, ['record' => $purchaseOrder->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'status' => PurchaseOrderStatus::SENT->value,
            'notes' => 'Updated notes',
        ]);
    }

    public function test_can_filter_purchase_orders_by_status(): void
    {
        $draftOrders = PurchaseOrder::factory()->count(3)->create([
            'status' => PurchaseOrderStatus::DRAFT,
        ]);
        $sentOrders = PurchaseOrder::factory()->count(2)->create([
            'status' => PurchaseOrderStatus::SENT,
        ]);

        Livewire::test(ListPurchaseOrders::class)
            ->filterTable('status', PurchaseOrderStatus::DRAFT->value)
            ->assertCanSeeTableRecords($draftOrders)
            ->assertCanNotSeeTableRecords($sentOrders);
    }

    public function test_can_filter_purchase_orders_by_supplier(): void
    {
        $supplier1 = Supplier::factory()->create();
        $supplier2 = Supplier::factory()->create();

        $ordersSupplier1 = PurchaseOrder::factory()->count(3)->create(['supplier_id' => $supplier1->id]);
        $ordersSupplier2 = PurchaseOrder::factory()->count(2)->create(['supplier_id' => $supplier2->id]);

        Livewire::test(ListPurchaseOrders::class)
            ->filterTable('supplier', $supplier1->id)
            ->assertCanSeeTableRecords($ordersSupplier1)
            ->assertCanNotSeeTableRecords($ordersSupplier2);
    }

    public function test_can_search_purchase_orders_by_po_number(): void
    {
        $purchaseOrders = PurchaseOrder::factory()->count(5)->create();
        $searchOrder = $purchaseOrders->first();

        Livewire::test(ListPurchaseOrders::class)
            ->searchTable($searchOrder->po_number)
            ->assertCanSeeTableRecords([$searchOrder])
            ->assertCanNotSeeTableRecords($purchaseOrders->skip(1));
    }

    public function test_purchase_order_workflow_from_draft_to_sent(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        // Create draft purchase order
        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::DRAFT,
        ]);

        PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $product->id,
            'ordered_quantity' => 100,
            'unit_price' => 50.00,
        ]);

        $this->assertEquals(PurchaseOrderStatus::DRAFT, $purchaseOrder->status);

        // Update status to sent
        Livewire::test(EditPurchaseOrder::class, ['record' => $purchaseOrder->getRouteKey()])
            ->fillForm(['status' => PurchaseOrderStatus::SENT->value])
            ->call('save')
            ->assertHasNoFormErrors();

        $purchaseOrder->refresh();
        $this->assertEquals(PurchaseOrderStatus::SENT, $purchaseOrder->status);
    }
}
