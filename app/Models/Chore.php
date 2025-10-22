<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chore extends Model
{
    /** @use HasFactory<\Database\Factories\ChoreFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'points',
        'family_id',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
