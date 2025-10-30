<?php

namespace App\Models\TransactionModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{

    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'invoice_no',
        'item_code',
        'item_name',
        'packing',
        'quantity',
        'price',
        'amount',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_no', 'invoice_no');
    }
}
