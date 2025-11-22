<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $costPrice = fake()->randomFloat(2, 5, 100);
        $salePrice = $costPrice * fake()->randomFloat(2, 1.2, 2.5); // 20% to 150% markup

        return [
            'name' => fake()->words(3, true),
            'code' => fake()->optional()->bothify('PROD-####'),
            'cost_price' => $costPrice,
            'sale_price' => round($salePrice, 2),
            'stock' => fake()->numberBetween(0, 200),
            'min_stock' => 5,
            'image_path' => fake()->optional()->imageUrl(),
            'is_active' => true,
        ];
    }
}

