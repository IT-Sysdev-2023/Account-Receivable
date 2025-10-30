<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAccountSummary extends Model
{
    protected $table = 'customer_account_summary';

    public $timestamps = false;

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'total_amount' => 'float',
        'current_balance' => 'float',
        'total_amount_paid' => 'float',
        'total_adjusted' => 'float',
        'total_shrinkage' => 'float',
        'total_overage' => 'float',
        'total_return' => 'float',
        'last_activity' => 'datetime',
    ];
}
