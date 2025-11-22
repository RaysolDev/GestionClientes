<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\InventoryMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create at least one user (seller/admin)
        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Create additional users if needed
        User::factory(2)->create();

        // Create 50 products
        $this->command->info('Creating 50 products...');
        $products = Product::factory(50)->create();

        // Create 20 clients
        $this->command->info('Creating 20 clients...');
        $clients = Client::factory(20)->create();

        // Create 100 past sales
        $this->command->info('Creating 100 past sales with details and inventory movements...');
        
        for ($i = 0; $i < 100; $i++) {
            DB::transaction(function () use ($products, $clients, $user) {
                // Select random client and user
                $client = $clients->random();
                $seller = User::inRandomOrder()->first();

                // Create sale with past date
                $saleDate = fake()->dateTimeBetween('-1 year', 'now');
                $sale = Sale::factory()->create([
                    'client_id' => $client->id,
                    'user_id' => $seller->id,
                    'sale_date' => $saleDate,
                    'status' => fake()->randomElement(['PAID', 'PENDING', 'CANCELED']),
                    'payment_method' => fake()->randomElement(['CASH', 'CARD', 'YAPE', 'PLIN', 'CREDIT']),
                ]);

                // Create 1-5 sale details per sale
                $numDetails = fake()->numberBetween(1, 5);
                $totalAmount = 0;

                for ($j = 0; $j < $numDetails; $j++) {
                    $product = $products->random();
                    $quantity = fake()->numberBetween(1, 5);
                    $unitPrice = $product->sale_price;
                    $subtotal = $quantity * $unitPrice;

                    // Create sale detail
                    $saleDetail = SaleDetail::factory()->create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);

                    $totalAmount += $subtotal;

                    // Update product stock (subtract)
                    $product->decrement('stock', $quantity);

                    // Create inventory movement
                    InventoryMovement::factory()->create([
                        'product_id' => $product->id,
                        'type' => 'OUT',
                        'quantity' => $quantity,
                        'reason' => 'SALE',
                        'related_sale_id' => $sale->id,
                    ]);
                }

                // Update sale total amount
                $sale->update(['total_amount' => round($totalAmount, 2)]);

                // Update client totals (only if sale is PAID)
                if ($sale->status === 'PAID') {
                    $client->increment('total_spent', $totalAmount);
                    if (!$client->last_purchase_date || $client->last_purchase_date < $saleDate) {
                        $client->update(['last_purchase_date' => $saleDate]);
                    }
                }
            });

            if (($i + 1) % 25 === 0) {
                $this->command->info("Created " . ($i + 1) . " sales...");
            }
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('- 50 Products created');
        $this->command->info('- 20 Clients created');
        $this->command->info('- 100 Sales with details and inventory movements created');
    }
}
