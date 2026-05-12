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

    public function getTitleAttribute($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return str_replace(
            [
                'Kiá»ƒm tra tá»•ng quÃ¡t',
                'Kiểm tra tá»•ng quÃ¡t',
                'Kiá»ƒm tra bÃªn trong khoang lÃ¡i',
                'Kiá»ƒm tra Ä‘á»™ng cÆ¡',
                'YÃªu cáº§u há»— trá»£',
            ],
            [
                'Kiểm tra tổng quát',
                'Kiểm tra tổng quát',
                'Kiểm tra bên trong khoang lái',
                'Kiểm tra động cơ',
                'Yêu cầu hỗ trợ',
            ],
            $value
        );
    }

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
