<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ledger extends Model
{
    use HasUlids;

    protected $fillable = [
        'family_id',
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

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
