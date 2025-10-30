<?php

namespace App\Services;

use App\Models\TransactionModels\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaymentNumberService
{
    protected const DEFAULT_START_NUMBER = 25000001;
    protected const API_ENDPOINT = 'http://10.233.1.60:81/farms-invoicing-breeder/transactionController/SalesInvoiceController/getLatestPaymentNo';

    public function generate(): int
    {
        $latest = Payment::withTrashed()
            ->lockForUpdate()
            ->orderByDesc('payment_no')
            ->first();

        $localNumber = $latest ? $latest->payment_no + 1 : self::DEFAULT_START_NUMBER;

        try {
            $apiNumber = $this->fetchFromApi();
            return max($localNumber, $apiNumber);
        } catch (\Exception $e) {
            return $localNumber;
        }
    }

    protected function fetchFromApi(): int
    {
        $response = Http::timeout(3)
            ->retry(2, 100)
            ->get(self::API_ENDPOINT);

        if ($response->successful()) {
            return (int)($response->json()['next_payment_no'] ?? 0);
        }

        throw new \Exception('Payment API request failed');
    }
}
