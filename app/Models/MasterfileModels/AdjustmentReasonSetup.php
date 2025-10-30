<?php

namespace App\Models\MasterfileModels;

use Illuminate\Database\Eloquent\Model;

class AdjustmentReasonSetup extends Model
{
    protected $table = 'adj_reason_setup';

    protected $fillable = [
        'reason_name',
        'acc_code',
        'type',
        'status',
        'created_by',
    ];
}
