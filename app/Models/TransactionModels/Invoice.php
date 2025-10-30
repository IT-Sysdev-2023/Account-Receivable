<?php

namespace App\Models\TransactionModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = "invoice";

    protected $fillable = [
        'invoice_no',
        'payment_no',
        'receipt_date',
        'transaction_date',
        'customer_code',
        'name',
        'price_group',
        'payment_mode',
        'chargeinvoice_type',
        'particular',
        'reference_no',
        'total_amount',
        'created_by',
        'exported',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_no', 'invoice_no');
    }

    // Handle soft delete cascading
    protected static function boot()
    {
        parent::boot();

        // When an invoice is soft-deleted, soft-delete its items
        static::deleting(function (Invoice $invoice) {
            if (!$invoice->isForceDeleting()) {
                $invoice->items()->delete();
            }
        });

        // When an invoice is restored, restore its items
        static::restoring(function (Invoice $invoice) {
            $invoice->items()->withTrashed()->restore();
        });
    }
}
