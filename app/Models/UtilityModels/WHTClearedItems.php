<?php

namespace App\Models\UtilityModels;

use Illuminate\Database\Eloquent\Model;

class WHTClearedItems extends Model
{
    protected $table = "wht_cleared_items";

    protected $fillable = [
        'wht_clearing_no',
        'payment_no',
        'wht_no',
        'document_no',
        'receipt_date',
        'amount',
        'status',
        'remarks',
    ];

    public function whtCleared()
    {
        return $this->belongsTo(WHTCleared::class, 'clearing_no', 'clearing_no');
    }
}
