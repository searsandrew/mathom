<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Redemption extends Model
{
    /** @use HasFactory<\Database\Factories\RedemptionFactory> */
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'status',
        'requested_at',
        'approved_at',
        'fulfilled_at',
        'cancelled_at',
    ];

    public function rewards(): BelongsToMany
    {
        return $this->belongsToMany(Reward::class)->withPivot(['quantity', 'unit_price', 'total_price']);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
