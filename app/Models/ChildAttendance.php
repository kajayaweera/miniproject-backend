<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildAttendance extends Model
{
    /** @use HasFactory<\Database\Factories\ChildAttendanceFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'attendance',
    ];

    protected $casts = [
        'date' => 'date',
        'attendance' => 'array',
    ];
}
