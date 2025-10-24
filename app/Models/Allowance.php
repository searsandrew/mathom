<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allowance extends Model
{
    /** @use HasFactory<\Database\Factories\AllowanceFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'family_id',
        'wallet_id',
        'status',
        'frequency',
        'day_of_week',
        'day_of_month',
        'rrule_text',
        'timezone',
        'mode',
        'fixed_points',
        'min_approved_occurrences',
        'bonus_points_on_threshold',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'fixed_points' => 'integer',
        'min_approved_occurrences' => 'integer',
        'bonus_points_on_threshold' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeForFamily($query, $familyId)
    {
        return $query->where('family_id', $familyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
