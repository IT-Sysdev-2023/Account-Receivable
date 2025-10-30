<?php

namespace App\Services;

use App\Models\TransactionModels\Adjustment;
use Illuminate\Support\Facades\DB;

class AdjustmentNumberService
{
    protected const DEFAULT_START_NUMBER = 25000001;

    public function generate(): int
    {
        $latest = Adjustment::withTrashed()
            ->lockForUpdate()
            ->orderByDesc('adjustment_no')
            ->first();

        return $latest ? $latest->adjustment_no + 1 : self::DEFAULT_START_NUMBER;
    }
}
