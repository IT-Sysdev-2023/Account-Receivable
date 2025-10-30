<?php

namespace App\Models\ReportModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerLedger extends Model
{

    use SoftDeletes;

    protected $table = "customer_ledger";

    protected $fillable = [
        'date',
        'invoice_number',
        'type',
        'customer_code',
        'customer_name',
        'currency',
        'amount',
        'adjusted_amount',
        'amount_paid',
        'running_balance',
        'trade_type',
        'shrinkage',
        'overage',
        'return',
        'si_payment_type',
    ];
}
