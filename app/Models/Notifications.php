<?php

namespace App\Models;

use App\Models\MasterfileModels\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notifications extends Model
{
    use SoftDeletes;

    protected $table = "notifications";

    protected $fillable = [
        "user_id",
        "message",
        "notified_at",
        "read",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
