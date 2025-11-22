<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->numerify('9########'),
            'email' => fake()->optional()->safeEmail(),
            'last_purchase_date' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
            'total_spent' => fake()->randomFloat(2, 0, 5000),
        ];
    }
}

