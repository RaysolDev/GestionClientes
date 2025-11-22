<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'total_amount' => fake()->randomFloat(2, 10, 1000),
            'status' => fake()->randomElement(['PAID', 'PENDING', 'CANCELED']),
            'payment_method' => fake()->randomElement(['CASH', 'CARD', 'YAPE', 'PLIN', 'CREDIT']),
            'sale_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}

