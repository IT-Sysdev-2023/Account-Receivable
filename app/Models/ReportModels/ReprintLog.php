<?php

namespace App\Models\ReportModels;

use Illuminate\Database\Eloquent\Model;

class ReprintLog extends Model
{
    protected $table = "reprint_log";

    protected $fillable = [
        'document_no',
        'person_authored',
        'type',
        'printed_date',
        'printed_time',
        'desktop_used',
    ];
}
