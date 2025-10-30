<?php

namespace App\Models\MasterfileModels;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'cus_id',
        'cus_code',
        'cus_name',
        'cus_type',
        'cus_price_group',
        'cus_address',
        'cus_tin',
        'cus_currency',
        'cus_bu',
        'nav_code',
        'credit_limit',
        'payment_terms',
        'non_trade',
        'applies_shrinkage',
        'editable_wht',
        'journal_voucher',
        'gen_posting',
        'cus_posting',
        'vat_posting',
        'cus_status',
        'setup_by',
        'advanced_payment_balance',
    ];
}
