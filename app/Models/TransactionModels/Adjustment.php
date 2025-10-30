<?php

namespace App\Models\TransactionModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Adjustment extends Model
{

    use SoftDeletes;

    protected $table = "adjustment";

    protected $fillable = [
        'adjustment_no',
        'receipt_date',
        'transaction_date',
        'customer_code',
        'name',
        'type',
        'apply_to',
        'invoice_no',
        'balance',
        'adjustment_reason',
        'particulars',
        'amount',
        'created_by',
        'exported',
    ];
}
