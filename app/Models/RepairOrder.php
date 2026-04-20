<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'track_id',
        'customer_id',
        'vehicle_id',
        'advisor_id',
        'status',
        'odometer_reading',
        'diagnosis_note',
        'subtotal', // New
        'discount_amount', // New
        'tax_amount', // New
        'promotion_id', // New
        'include_vhc',
        'payment_status',
        'payment_method',
        'notes',
        'customer_note',
        'total_amount',
        'expected_completion_date',
    ];

    protected $casts = [
        'expected_completion_date' => 'datetime',
    ];

    // Relations
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function items()
    {
        return $this->hasMany(RepairOrderItem::class);
    }
    
    public function tasks()
    {
        return $this->hasMany(RepairTask::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
    
    public function vhcReport()
    {
        return $this->hasOne(VhcReport::class);
    }
}
