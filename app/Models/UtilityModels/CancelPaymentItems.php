<?php

namespace App\Models\UtilityModels;

use Illuminate\Database\Eloquent\Model;

class CancelPaymentItems extends Model
{
    protected $table = "cancelled_payment_items";

    protected $fillable = [
        'cancellation_no',
        'document_no',
        'payment_no',
        'receipt_date',
        'payment_type',
        'amount',
        'remarks',
    ];

    public function whtCleared()
    {
        return $this->belongsTo(CancelPayment::class, 'cancellation_no', 'cancellation_no');
    }
}
