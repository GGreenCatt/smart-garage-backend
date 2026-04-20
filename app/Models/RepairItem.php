<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairItem extends Model
{
    protected $fillable = ['repair_order_id', 'name', 'sku', 'type', 'qty', 'price', 'status'];

    protected $appends = ['total'];

    public function getTotalAttribute()
    {
        return $this->qty * $this->price;
    }

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }
}
