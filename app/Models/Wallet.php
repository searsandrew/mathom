<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    /** @use HasFactory<\Database\Factories\WalletFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'family_id',
        'user_id',
        'balance',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function ledger(): HasMany
    {
        return $this->hasMany(Ledger::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
