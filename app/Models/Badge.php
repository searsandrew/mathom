<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    /** @use HasFactory<\Database\Factories\BadgeFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'family_id',
        'name',
        'slug',
        'description',
        'image',
        'criteria',
    ];

    protected $casts = [
        'criteria' => 'array',
    ];

    public function scopeForFamily($query, $familyId)
    {
        return $query->where('family_id', $familyId);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('awarded_at', 'reason');
    }
}
