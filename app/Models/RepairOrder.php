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
        'quote_status',
        'quote_sent_at',
        'odometer_reading',
        'diagnosis_note',
        'subtotal', // New
        'discount_amount', // New
        'tax_amount', // New
        'promotion_id', // New
        'include_vhc',
        'payment_status',
        'payment_method',
        'service_type',
        'start_time',
        'notes',
        'customer_note',
        'total_amount',
        'expected_completion_date',
    ];

    protected $casts = [
        'expected_completion_date' => 'datetime',
        'quote_sent_at' => 'datetime',
        'start_time' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public function isLockedForStaffChanges(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true)
            || $this->payment_status === 'paid';
    }

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
