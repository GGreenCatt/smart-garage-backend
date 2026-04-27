<?php

namespace App\Providers;

use App\Models\Role;
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

        foreach (Role::permissions() as $permission) {
            \Illuminate\Support\Facades\Gate::define($permission, function ($user) use ($permission) {
                return $user->hasPermission($permission);
            });
        }
    }
}
