<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_plate',
        'make',
        'model',
        'type',
        'year',
        'color',
        'vin',
        'user_id',
        'owner_phone',
        'owner_name',
    ];

    public function repairOrders()
    {
        return $this->hasMany(RepairOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_phone', 'phone');
    }
}
