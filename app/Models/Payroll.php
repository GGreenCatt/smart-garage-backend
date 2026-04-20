<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = ['user_id', 'month', 'base_salary', 'bonus', 'deductions', 'total_hours', 'overtime_hours', 'performance_score'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
