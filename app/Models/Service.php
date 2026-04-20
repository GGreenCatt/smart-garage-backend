<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'code', 'name', 'category', 'base_price', 
        'description', 'estimated_duration'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];
}
