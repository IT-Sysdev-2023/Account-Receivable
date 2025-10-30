<?php

namespace App\Services;

use App\Models\TransactionModels\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    protected const DEFAULT_START_NUMBER = 25000001;

    public function generate(): int
    {
        $latest = Invoice::withTrashed()
            ->lockForUpdate()
            ->orderByDesc('invoice_no')
            ->first();

        return $latest ? $latest->invoice_no + 1 : self::DEFAULT_START_NUMBER;
    }
}
