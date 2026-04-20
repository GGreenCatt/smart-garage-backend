<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Observers
        \App\Models\Appointment::observe(\App\Observers\NotificationObserver::class);
        \App\Models\MaterialRequest::observe(\App\Observers\NotificationObserver::class);

        // Admin Super Power
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        $permissions = [
            'view_dashboard',
            'manage_settings',
            'view_staff',
            'manage_staff',
            'manage_customers',
            'manage_vehicles',
            'view_inventory',
            'manage_inventory',
            'view_reports',
            'delete_vehicles',
            'manage_appointments',
            'view_repair_orders',
            'create_repair_orders',
            'manage_sos',
            'view_requests'
        ];

        foreach ($permissions as $permission) {
            \Illuminate\Support\Facades\Gate::define($permission, function ($user) use ($permission) {
                return $user->hasPermission($permission);
            });
        }
    }
}
