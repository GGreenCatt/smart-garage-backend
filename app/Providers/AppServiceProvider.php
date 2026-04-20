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
            'delete_vehicles'
        ];

        foreach ($permissions as $permission) {
            \Illuminate\Support\Facades\Gate::define($permission, function ($user) use ($permission) {
                return $user->isAdmin() || $user->hasPermission($permission);
            });
        }
    }
}
