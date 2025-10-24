<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reward extends Model
{
    /** @use HasFactory<\Database\Factories\RewardFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'description',
        'family_id',
        'image_path',
        'image_name',
        'is_active',
        'inventory',
        'price_points',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function redemptions(): BelongsToMany
    {
        return $this->belongsToMany(Redemption::class)->withPivot(['quantity', 'unit_price', 'total_price']);
    }
}
