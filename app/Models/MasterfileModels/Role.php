<?php

namespace App\Models\MasterfileModels;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot([
            'can_view',
            'can_insert',
            'can_update',
            'can_delete',
            'can_print',
            'can_tag',
            'can_reprint'
        ]);
    }

    public function rolePermissions()
    {
        return $this->hasMany(Permission::class);
    }
}
