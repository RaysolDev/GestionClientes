<?php

namespace Database\Factories;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    protected $model = InventoryMovement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $type = fake()->randomElement(['IN', 'OUT']);
        $reason = $type === 'OUT' 
            ? fake()->randomElement(['SALE', 'RETURN'])
            : fake()->randomElement(['PURCHASE', 'ADJUSTMENT']);

        return [
            'product_id' => Product::factory(),
            'type' => $type,
            'quantity' => fake()->numberBetween(1, 50),
            'reason' => $reason,
            'related_sale_id' => $type === 'OUT' && $reason === 'SALE' 
                ? Sale::factory() 
                : null,
        ];
    }
}

