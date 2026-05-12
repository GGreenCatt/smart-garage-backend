<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'start_date',
        'end_date',
        'usage_limit',
        'used_count',
        'customer_id',
        'vehicle_id',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function isValid()
    {
        return $this->statusReason() === 'active';
    }

    public function statusReason(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->start_date && now()->lt($this->start_date)) {
            return 'scheduled';
        }

        if ($this->end_date && now()->gt($this->end_date)) {
            return 'expired';
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return 'exhausted';
        }

        return 'active';
    }
}
