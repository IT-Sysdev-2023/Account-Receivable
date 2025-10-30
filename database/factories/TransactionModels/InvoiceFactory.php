<?php

namespace Database\Factories\TransactionModels;

use Illuminate\Support\Str;
use App\Models\TransactionModels\Invoice;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionModels\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Invoice::class;
    public function definition(): array
    {
        return [
            'invoice_no' => strtoupper(Str::random(10)),
            'payment_no' => rand(0, 1) ? strtoupper(Str::random(8)) : null,
            'receipt_date' => $this->faker->date(),
            'transaction_date' => $this->faker->date(),
            'customer_code' => 'CUST-' . $this->faker->unique()->numerify('########'),
            'name' => $this->faker->name(),
            'price_group' => $this->faker->randomElement(['Retail', 'Wholesale', 'Special']),
            'payment_mode' => $this->faker->randomElement(['Account Receivables', 'Cash']),
            'chargeinvoice_type' => $this->faker->randomElement(['Standard', 'Advance', 'Partial']),
            'particular' => $this->faker->sentence(),
            'reference_no' => 'REF-' . strtoupper(Str::random(8)),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
            'created_by' => $this->faker->userName(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
