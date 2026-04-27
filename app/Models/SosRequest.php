<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Vehicle;

class SosRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'guest_name',
        'guest_phone',
        'vehicle_id',
        'latitude',
        'longitude',
        'description',
        'images',
        'status',
        'assigned_staff_id',
        'completed_at'
    ];

    public function getDisplayNameAttribute()
    {
        return $this->customer_id
            ? ($this->customer->name ?? 'Khách hàng mới')
            : ($this->guest_name ?: 'Khách vãng lai');
    }

    public function getDisplayPhoneAttribute()
    {
        return $this->customer_id
            ? ($this->customer->phone ?? 'Không có SĐT')
            : ($this->guest_phone ?: 'Không có SĐT');
    }

    protected $casts = [
        'images' => 'array',
        'completed_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

