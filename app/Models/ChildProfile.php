<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildProfile extends Model
{
    /** @use HasFactory<\Database\Factories\ChildProfileFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user(){

        return $this->belongsTo(User::class);
    }
}
