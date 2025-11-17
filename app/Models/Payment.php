<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;
    
     protected $fillable = [
        'user_id',
        'courses',
        'total_amount',
        'status'
    ];

    protected $casts = [
        'courses' => 'array', // This will automatically handle JSON encoding/decoding
        'total_amount' => 'decimal:2'
    ];

    public function user(){

        return $this->belongsTo(User::class);
    }
}
