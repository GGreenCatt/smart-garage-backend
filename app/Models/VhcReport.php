<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VhcReport extends Model
{
    use HasFactory;

    protected $fillable = ['repair_order_id', 'status'];

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function defects()
    {
        return $this->hasMany(VhcDefect::class);
    }
}
