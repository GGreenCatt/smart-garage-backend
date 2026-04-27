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
        'latitude',
        'longitude',
        'is_sharing_location',
        'last_location_update',
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
            'is_sharing_location' => 'boolean',
            'last_location_update' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function repairOrders()
    {
        return $this->hasMany(RepairOrder::class, 'customer_id');
    }

    public function assignedRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function hasPermission($permission)
    {
        $permissions = array_values(array_unique(array_merge(
            $this->assignedRole->permissions ?? [],
            $this->permissions ?? []
        )));

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public function isAdmin()
    {
        return $this->role === 'admin' || ($this->assignedRole && $this->assignedRole->slug === 'admin');
    }

    public function isManager()
    {
        return $this->role === 'manager' || ($this->assignedRole && $this->assignedRole->slug === 'manager');
    }

    public function isTechnician()
    {
        return $this->role === 'technician' || ($this->assignedRole && $this->assignedRole->slug === 'technician');
    }

    public function isStaffAdvisor()
    {
        return $this->role === 'staff' || ($this->assignedRole && $this->assignedRole->slug === 'staff');
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
