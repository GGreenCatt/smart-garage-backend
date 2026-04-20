<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    protected $fillable = ['user_id', 'date', 'shift_type', 'hours', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
