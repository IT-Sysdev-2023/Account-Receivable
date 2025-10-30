<?php

namespace Database\Factories\TransactionModels;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TransactionModels\InvoiceItem;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionModels\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 20);
        $price = $this->faker->randomFloat(2, 10, 500);

        return [
            'invoice_no' => null, // We'll assign this in the seeder
            'item_code' => 'ITM-' . $this->faker->unique()->numerify('########'),
            'item_name' => $this->faker->words(3, true),
            'packing' => $this->faker->randomElement(['Box', 'Pack', 'Piece']),
            'quantity' => $quantity,
            'price' => $price,
            'amount' => $quantity * $price,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
