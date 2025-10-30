<?php

namespace Database\Seeders;

use App\Models\TransactionModels\Invoice;
use App\Models\TransactionModels\InvoiceItem;
use App\Services\InvoiceNumberService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = app(InvoiceNumberService::class); // Resolve the service from the container
        DB::transaction(function () use ($service) {
            for ($i = 0; $i < 10000; $i++) {
                $invoice_no = $service->generate();

                $invoice = Invoice::factory()->create([
                    'invoice_no' => $invoice_no,
                ]);

                InvoiceItem::factory()
                    ->count(rand(1, 5))
                    ->state([
                        'invoice_no' => $invoice_no,
                    ])
                    ->create();

                DB::table('customer_ledger')->insert([
                    'invoice_number' => $invoice_no,
                    'date' => $invoice->transaction_date,
                    'type' => 'Charge Invoice',
                    'customer_code' => $invoice->customer_code,
                    'customer_name' => $invoice->name,
                    'currency' => 'PHP', 
                    'amount' => $invoice->total_amount,
                    'adjusted_amount' => $invoice->total_amount,
                    'amount_paid' => 0.00,
                    'running_balance' => $invoice->total_amount,
                    'trade_type' => null,
                    'shrinkage' => 0,
                    'overage' => 0,
                    'return' => 0,
                    'si_payment_type' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
