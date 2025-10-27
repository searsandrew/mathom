<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Redemption extends Model
{
    /** @use HasFactory<\Database\Factories\RedemptionFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'family_id',
        'wallet_id',
        'status',
        'requested_at',
        'approved_at',
        'fulfilled_at',
        'cancelled_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function rewards(): BelongsToMany
    {
        return $this->belongsToMany(Reward::class)->withPivot(['quantity', 'unit_price', 'total_price']);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function ledgerHold(): HasOne { return $this->hasOne(Ledger::class, 'reference_id')->where('reference_type', 'redemption')->where('type', 'redeem_hold'); }
    public function ledgerReleases(): HasMany { return $this->hasMany(Ledger::class, 'reference_id')->where('reference_type', 'redemption')->where('type', 'redeem_release'); }
    public function ledgerCaptures(): HasMany { return $this->hasMany(Ledger::class, 'reference_id')->where('reference_type', 'redemption')->where('type', 'redeem_capture');}
}
