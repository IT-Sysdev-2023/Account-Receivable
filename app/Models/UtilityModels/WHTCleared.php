<?php

namespace App\Models\UtilityModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WHTCleared extends Model
{
    use SoftDeletes;

    protected $table = "wht_cleared";

    protected $fillable = [
        'wht_clearing_no',
        'transaction_date',
        'clearing_date',
        'customer_code',
        'customer_name',
        'created_by',
    ];

    public function whtClearedItems()
    {
        return $this->hasMany(whtClearedItems::class, 'wht_clearing_no', 'wht_clearing_no');
    }
}
