<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VhcDefect extends Model
{
    use HasFactory;

    protected $fillable = [
        'vhc_report_id',
        'title',
        'description',
        'type',
        'severity',
        'pos_x',
        'pos_y',
        'pos_z',
        'images'
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function report()
    {
        return $this->belongsTo(VhcReport::class);
    }
}
