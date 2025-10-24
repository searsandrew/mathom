<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'applies_to',
    ];

    public function chores(): BelongsToMany
    {
        return $this->belongsToMany(Chore::class);
    }

    public function rewards(): BelongsToMany
    {
        return $this->belongsToMany(Reward::class);
    }
}
