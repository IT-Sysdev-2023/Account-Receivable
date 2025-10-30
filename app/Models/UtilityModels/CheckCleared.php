<?php

namespace App\Models\UtilityModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckCleared extends Model
{
    use SoftDeletes;

    protected $table = "check_cleared";

    protected $fillable = [
        'clearing_no',
        'transaction_date',
        'clearing_date',
        'check_type',
        'customer_code',
        'customer_name',
        'created_by',
    ];

    public function checkClearedItems()
    {
        return $this->hasMany(CheckClearedItems::class, 'clearing_no', 'clearing_no');
    }
}
