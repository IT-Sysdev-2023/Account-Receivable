<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccCode extends Model
{
    protected $table = "acc_code";

    protected $fillable = [
        "gl_account_id",
        "gl_account_navcode",
        "gl_account_name",
        "setup_by",
        "status",
        "business_unit",
    ];
}
