<?php

namespace App\Models\MasterfileModels;

use Illuminate\Database\Eloquent\Model;

class ItemWholeSale extends Model
{

    protected $table = 'item_wholesale';

    protected $fillable = [
        'item_id', 'groupcode', 'packing', 'price',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
