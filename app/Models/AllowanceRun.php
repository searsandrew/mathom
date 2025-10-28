<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowanceRun extends Model
{
    protected $fillable = [
        'allowance_id',
        'started_at',
        'ended_at',
        'status',
        'ledger_id',
        'calc_summary',
    ];
}
