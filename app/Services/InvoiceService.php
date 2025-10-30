<?php

namespace App\Services;

use App\Models\TransactionModels\Adjustment;
use App\Models\TransactionModels\Invoice;
use App\Models\TransactionModels\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class InvoiceService
{
    /**
     * Get customer by customer code
     */
    public static function getNextInvoiceNumber()
    {
        return DB::transaction(function () {
            $latestInvoice = Invoice::withTrashed()
                ->lockForUpdate()
                ->orderByDesc('invoice_no')
                ->first();

            return $latestInvoice ? $latestInvoice->invoice_no + 1 : 25000001;
        });
    }

    public static function generateNextPaymentNumber()
    {
        return DB::transaction(function () {
            $latestPayment = Payment::withTrashed()
                ->lockForUpdate()
                ->orderByDesc('payment_no')
                ->first();

            $localNextNumber = $latestPayment ? $latestPayment->payment_no + 1 : 25000001;

            try {
                $response = Http::timeout(3)
                    ->retry(2, 100)
                    ->get('http://10.233.1.60:81/farms-invoicing-breeder/transactionController/SalesInvoiceController/getLatestPaymentNo');
                if ($response->successful()) {
                    $apiData = $response->json();
                    $apiNextNumber = $apiData['next_payment_no'] ?? 0;
                    $nextNumber = max($localNextNumber, $apiNextNumber);
                } else {
                    $nextNumber = $localNextNumber;
                }
            } catch (\Exception $e) {
                $nextNumber = $localNextNumber;
            }

            return $nextNumber;
        });
    }

    public static function getNextAdjustmentNumber()
    {
        return DB::transaction(function () {
            $latestAdjustment = Adjustment::withTrashed()
                ->lockForUpdate()
                ->orderByDesc('adjustment_no')
                ->first();

            return $latestAdjustment ? $latestAdjustment->adjustment_no + 1 : 25000001;
        });
    }

    /**
     * Get all customers
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Invoice::all();
    }
}
