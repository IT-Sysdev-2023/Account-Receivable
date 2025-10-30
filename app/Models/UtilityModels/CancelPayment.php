<?php

namespace App\Models\UtilityModels;

use Illuminate\Database\Eloquent\Model;

class CancelPayment extends Model
{
    protected $table = "cancelled_payments";

    protected $fillable = [
        'cancellation_no',
        'payment_no',
        'document_no',
        'type',
        'customer_code',
        'customer_name',
        'created_by',
    ];

    public function cancelledPaymentItems()
    {
        return $this->hasMany(CancelPaymentItems::class, 'cancellation_no', 'cancellation_no');
    }
}
