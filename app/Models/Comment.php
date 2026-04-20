<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['repair_order_id', 'user_id', 'content', 'is_internal', 'parent_id', 'attachment_path'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
