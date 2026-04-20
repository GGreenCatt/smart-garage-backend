<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairOrderItem extends Model
{
    protected $fillable = [
        'repair_order_id',
        'repair_task_id',
        'itemable_type',
        'itemable_id',
        'name',
        'quantity',
        'unit_price',
        'cost_price',
        'subtotal',
    ];

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function itemable()
    {
        return $this->morphTo();
    }
}
