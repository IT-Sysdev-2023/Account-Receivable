<?php

namespace App\Models\MasterfileModels;

use Illuminate\Database\Eloquent\Model;

class ChargeInvoiceType extends Model
{
    protected $table = 'charge_invoice_type';

    protected $fillable = [
        'sequence_no',
        'ci_type',
        'nav_code',
        'created_by'
    ];
}
