<?php

namespace App\Services;

use App\Models\UtilityModels\WHTCleared;
use Illuminate\Support\Facades\DB;

class WHTClearedNumberService
{
    protected const DEFAULT_START_NUMBER = 25000001;

    public function generate(): int
    {
        $latest = WHTCleared::withTrashed()
            ->lockForUpdate()
            ->orderByDesc('wht_clearing_no')
            ->first();

        return $latest ? $latest->wht_clearing_no + 1 : self::DEFAULT_START_NUMBER;
    }
}
