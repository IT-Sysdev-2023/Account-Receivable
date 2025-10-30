<?php

namespace App\Models\TransactionModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagerKeyEntries extends Model
{
    protected $table = "manager_key_entries";

    protected $fillable = ['user_id', 'user_name', 'entered_at'];
}
