<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllowanceRun extends Model
{
    use HasUlids;
    /**
     * The allowance runs table does not have timestamps.
     */
    public $timestamps = false;

    protected $fillable = [
        'allowance_id',
        'started_at',
        'ended_at',
        'status',
        'ledger_id',
        'calc_summary',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'calc_summary' => 'array',
    ];

    public function allowance(): BelongsTo
    {
        return $this->belongsTo(Allowance::class);
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }
}
