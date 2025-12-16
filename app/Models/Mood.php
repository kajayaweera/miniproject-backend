<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mood extends Model
{
    /** @use HasFactory<\Database\Factories\MoodFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'mood',
    ];

    protected $casts = [
        'date' => 'date',
        'mood' => 'array',
    ];
}
