<?php

namespace Tests\Feature\Filament;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class ProductResourceTest extends TestCase
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

    public function test_can_render_product_list_page(): void
    {
        $this->get(ProductResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_list_products(): void
    {
        $products = Product::factory()->count(10)->create();

        Livewire::test(ListProducts::class)
            ->assertCanSeeTableRecords($products);
    }

    public function test_can_render_product_create_page(): void
    {
        $this->get(ProductResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_can_create_product(): void
    {
        $category = ProductCategory::factory()->create();
        $newData = [
            'sku' => 'TEST-SKU-001',
            'name' => 'Test Product',
            'description' => 'Test product description',
            'unit' => 'pcs',
            'purchase_price' => 100.00,
            'selling_price' => 150.00,
            'category_id' => $category->id,
            'minimum_stock' => 10,
            'rack_location' => 'R01-S01',
        ];

        Livewire::test(CreateProduct::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-SKU-001',
            'name' => 'Test Product',
            'unit' => 'pcs',
        ]);
    }

    public function test_can_validate_product_input(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'sku' => '',
                'name' => '',
                'unit' => '',
                'purchase_price' => '',
                'selling_price' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['sku', 'name', 'unit', 'purchase_price', 'selling_price']);
    }

    public function test_can_render_product_edit_page(): void
    {
        $product = Product::factory()->create();

        $this->get(ProductResource::getUrl('edit', ['record' => $product]))
            ->assertSuccessful();
    }

    public function test_can_retrieve_product_data_in_edit_page(): void
    {
        $product = Product::factory()->create();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->assertFormSet([
                'sku' => $product->sku,
                'name' => $product->name,
                'unit' => $product->unit,
            ]);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create();

        $newData = [
            'name' => 'Updated Product Name',
            'selling_price' => 200.00,
        ];

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'selling_price' => 200.00,
        ]);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($product);
    }

    public function test_can_search_products_by_sku(): void
    {
        $products = Product::factory()->count(5)->create();
        $searchProduct = $products->first();

        Livewire::test(ListProducts::class)
            ->searchTable($searchProduct->sku)
            ->assertCanSeeTableRecords([$searchProduct])
            ->assertCanNotSeeTableRecords($products->skip(1));
    }

    public function test_can_search_products_by_name(): void
    {
        $products = Product::factory()->count(5)->create();
        $searchProduct = $products->first();

        Livewire::test(ListProducts::class)
            ->searchTable($searchProduct->name)
            ->assertCanSeeTableRecords([$searchProduct]);
    }

    public function test_can_filter_products_by_category(): void
    {
        $category1 = ProductCategory::factory()->create();
        $category2 = ProductCategory::factory()->create();

        $productsCategory1 = Product::factory()->count(3)->create(['category_id' => $category1->id]);
        $productsCategory2 = Product::factory()->count(2)->create(['category_id' => $category2->id]);

        Livewire::test(ListProducts::class)
            ->filterTable('category', $category1->id)
            ->assertCanSeeTableRecords($productsCategory1)
            ->assertCanNotSeeTableRecords($productsCategory2);
    }
}
