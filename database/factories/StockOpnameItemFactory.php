<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockOpnameItem>
 */
class StockOpnameItemFactory extends Factory
{
    protected $model = StockOpnameItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $systemStock = fake()->numberBetween(0, 1000);
        $physicalStock = fake()->numberBetween(0, 1000);
        $variance = $physicalStock - $systemStock;

        return [
            'stock_opname_id' => StockOpname::factory(),
            'product_id' => Product::factory(),
            'system_stock' => $systemStock,
            'physical_stock' => $physicalStock,
            'variance' => $variance,
        ];
    }
}
