<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ledger extends Model
{
    protected $fillable = [
        'wallet_id',
        'occurred_at',
        'type',
        'amount',
        'reference_type',
        'reference_id',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
