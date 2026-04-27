<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = ['repair_order_id', 'customer_id', 'guest_session_id', 'status'];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class, 'repair_order_id');
    }
}
