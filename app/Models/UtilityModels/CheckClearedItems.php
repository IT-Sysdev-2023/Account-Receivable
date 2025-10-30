<?php

namespace App\Models\UtilityModels;

use Illuminate\Database\Eloquent\Model;

class CheckClearedItems extends Model
{
    protected $table = "check_cleared_items";

    protected $fillable = [
        'clearing_no',
        'payment_no',
        'check_no',
        'document_no',
        'due_date',
        'amount',
        'status',
        'remarks',
    ];

    public function checkCleared()
    {
        return $this->belongsTo(CheckCleared::class, 'clearing_no', 'clearing_no');
    }
}
