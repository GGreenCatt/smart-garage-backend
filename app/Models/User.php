<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Legacy support
        'role_id',
        'phone',
        'address', // Enhanced schema
        'status',  // Enhanced schema (active/banned)
        'avatar',  // Enhanced schema
        'permissions', // Legacy support
        'tags',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'tags' => 'array',
        ];
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function assignedRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function hasPermission($permission)
    {
        return $this->assignedRole && in_array($permission, $this->assignedRole->permissions ?? []);
    }

    public function isAdmin()
    {
        return $this->role === 'admin' || ($this->assignedRole && $this->assignedRole->slug === 'admin');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }
}
