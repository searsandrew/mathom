<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'family_id',
        'name',
        'slug',
        'applies_to',
    ];

    protected $casts = [
        'applies_to' => 'string',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function chores(): BelongsToMany
    {
        return $this->belongsToMany(Chore::class);
    }

    public function rewards(): BelongsToMany
    {
        return $this->belongsToMany(Reward::class);
    }

    public function scopeForFamily($query, $familyId)
    {
        return $query->where('family_id', $familyId);
    }

    public function scopeAppliesTo($query, string $type)
    {
        return $query->whereIn('applies_to', [$type, 'both']);
    }
}
