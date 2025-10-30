<?php

namespace App\Models\TransactionModels;

use Illuminate\Database\Eloquent\Model;

class PaymentDetails extends Model
{
    protected $table = "payment_details";

    protected $fillable = [
        'payment_no',
        'check_no',
        'document_no',
        'document_date',
        'payment_receipt_date',
        'payment_date',
        'payment_type',
        'type',
        'customer_code',
        'customer_name',
        'check_type',
        'advpy_amount_paid',
        'amount',
        'balance',
        'amount_paid',
        'due_date',
        'clearing_date',
        'status',
        'remarks',
        'created_by',
    ];
}
