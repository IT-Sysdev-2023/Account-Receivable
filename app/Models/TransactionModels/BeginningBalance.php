<?php

namespace App\Models\TransactionModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeginningBalance extends Model
{

    use SoftDeletes;

    protected $table = "beginning_balance";

    protected $fillable = [
        'beginningbalance_no',
        'receipt_date',
        'transaction_date',
        'customer_code',
        'name',
        'particular',
        'balance_amount',
        'file',
        'created_by',
    ];
}
