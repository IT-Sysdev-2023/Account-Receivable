<?php

namespace App\Models\MasterfileModels;

use Illuminate\Database\Eloquent\Model;

class PackingType extends Model
{
    protected $table = 'packing_type';

    protected $fillable = ['sequence_no', 'packing_type', 'created_by'];
}
