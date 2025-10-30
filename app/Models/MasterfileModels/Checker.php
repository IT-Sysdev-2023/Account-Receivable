<?php

namespace App\Models\MasterfileModels;

use Illuminate\Database\Eloquent\Model;

class Checker extends Model
{
    protected $table = 'checkers';

    protected $fillable = [
        'photo',
        'first_name',
        'last_name',  
        'created_by',  
    ];
}
