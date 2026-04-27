<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'group', 'payload'];

    protected $casts = [
        'payload' => 'array',
    ];

    // Helper to get value
    public static function get($key, $default = null)
    {
        if (! Schema::hasTable('settings')) {
            return $default;
        }

        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    // Helper to set value
    public static function set($key, $value, $group = 'general')
    {
        if (! Schema::hasTable('settings')) {
            return null;
        }

        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }
}
