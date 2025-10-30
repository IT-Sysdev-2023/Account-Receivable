<?php

namespace App\Models\MasterfileModels;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    // The table associated with the model
    protected $table = 'permissions'; // Use the 'permissions' table

    // The attributes that are mass assignable
    protected $fillable = [
        'user_id',
        'role_id',
        'can_view',
        'can_insert',
        'can_update',
        'can_delete',
        'can_print',
        'can_tag',
        'can_reprint'
    ];

    public $timestamps = true;
}
