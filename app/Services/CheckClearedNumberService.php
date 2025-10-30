<?php

namespace App\Services;

use App\Models\UtilityModels\CheckCleared;
use Illuminate\Support\Facades\DB;

class CheckClearedNumberService
{
    protected const DEFAULT_START_NUMBER = 25000001;

    public function generate(): int
    {
        $latest = CheckCleared::withTrashed()
            ->lockForUpdate()
            ->orderByDesc('clearing_no')
            ->first();

        return $latest ? $latest->clearing_no + 1 : self::DEFAULT_START_NUMBER;
    }
}
