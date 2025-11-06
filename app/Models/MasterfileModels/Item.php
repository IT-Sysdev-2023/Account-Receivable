<?php

namespace App\Models\MasterfileModels;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'Item';

    protected $fillable = [
        'item_photo',
        'code',
        'setup_date',
        'name',
        'description',
        'type',
        'acc_code',
        'packing',
        'created_by',
    ];

    protected $casts = [
        'packing' => 'array',
    ];

    public function packings()
    {
        return $this->hasMany(ItemPacking::class);
    }

    // public function wholesales()
    // {
    //     return $this->hasMany(ItemWholeSale::class);
    // }
}
