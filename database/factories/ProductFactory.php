<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'sku' => 'SKU-' . $this->faker->unique()->numerify('######'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'unit' => $this->faker->randomElement(['pcs', 'box', 'kg', 'liter']),
            'purchase_price' => $this->faker->randomFloat(2, 10, 1000),
            'selling_price' => $this->faker->randomFloat(2, 15, 1500),
            'category_id' => ProductCategory::factory(),
            'minimum_stock' => $this->faker->numberBetween(5, 50),
            'rack_location' => $this->faker->bothify('R##-S##'),
        ];
    }
}
