<?php

namespace App\Models\TransactionModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $table = "payment";

    protected $fillable = [
        'payment_no',
        'receipt_date',
        'transaction_date',
        'customer_code',
        'name',
        'payment_type',
        'type',
        'reference_no',
        'ds_no',
        'document_no',
        'document_date',
        'advpy_amount_paid',
        'total_amount',
        'amount_paid',
        'acc_code',
        'cust_code',
        'cash_in_bank',
        'withBIR',
        'witholdingtax',
        'check_type',
        'aging_basis',
        'aging_days',
        'acc_name_address',
        'referral_name',
        'acc_number',
        'due_date',
        'created_by',
        'exported',
    ];

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetails::class, 'payment_no', 'payment_no');
    }
}
