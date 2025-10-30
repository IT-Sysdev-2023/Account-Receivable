<?php

namespace App\Models\MasterfileModels;

use Illuminate\Database\Eloquent\Model;

class ItemPacking extends Model
{
    protected $fillable = [
        'item_id', 'groupcode', 'packing', 'price', 'quantity', 'status',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
