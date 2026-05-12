<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'data' => 'array',
        'read_at' => 'datetime'
    ];

    protected static function booted(): void
    {
        static::creating(function (self $notification) {
            if (empty($notification->id)) {
                $notification->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function setAttribute($key, $value)
    {
        if ($key === 'id' && empty($value)) {
            $value = (string) \Illuminate\Support\Str::uuid();
        }
        parent::setAttribute($key, $value);
    }

    public function notifiable()
    {
        return $this->morphTo();
    }
}
