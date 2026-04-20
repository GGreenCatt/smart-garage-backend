<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class MaterialRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'repair_order_id', // Linked Job
        'part_name',
        'quantity',
        'cost_price', // Import
        'unit_price', // Selling
        'reason',
        'status',
        'admin_note'
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }
}
