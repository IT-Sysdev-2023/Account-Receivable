<?php

namespace App\Models\MasterfileModels;

use Illuminate\Database\Eloquent\Model;

class ShortageAmount extends Model
{
    protected $table = "shortage_amount";

    protected $fillable = ['shortage_amnt', 'created_by'];
}
