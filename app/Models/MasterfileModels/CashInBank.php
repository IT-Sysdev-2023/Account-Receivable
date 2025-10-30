<?php

namespace App\Models\MasterfileModels;

use Illuminate\Database\Eloquent\Model;

class CashInBank extends Model
{
    protected $table = 'cash_in_bank';

    protected $fillable = [
        'bank_code',
        'bank_name',
        'address',
        'acc_code',
        'acc_classification',
        'created_by'
    ];
}
