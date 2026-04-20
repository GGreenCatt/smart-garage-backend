<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_order_id',
        'title',
        'status',
        'customer_approval_status',
        'parent_id',
        'type',
        'mechanic_id',
        'service_id',
        'labor_cost',
        'severity',
        'description',
    ];

    public function children()
    {
        return $this->hasMany(RepairTask::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(RepairTask::class, 'parent_id');
    }

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }
    
    public function mechanic()
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function items()
    {
        return $this->hasMany(RepairOrderItem::class, 'repair_task_id');
    }
}
