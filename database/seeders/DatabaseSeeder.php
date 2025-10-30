<?php

namespace Database\Seeders;

use App\Models\MasterfileModels\User;
use App\Models\TransactionModels\Invoice;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Invoice::factory(1)->create([
        //     'invoice_no' => '25000000'
        // ]);
        //User::factory(9)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            InvoiceSeeder::class,
        ]);
    }
}
