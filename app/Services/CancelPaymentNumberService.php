<?php

namespace App\Services;

use App\Models\UtilityModels\CancelPayment;
use Illuminate\Support\Facades\DB;

class CancelPaymentNumberService
{
    protected const DEFAULT_START_NUMBER = 25000001;

    public function generate(): int
    {
        $latest = CancelPayment::orderByDesc('cancellation_no')
            ->lockForUpdate()
            ->first();

        return $latest ? $latest->cancellation_no + 1 : self::DEFAULT_START_NUMBER;
    }
}
