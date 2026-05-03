<?php

namespace App\Providers;

use App\Models\Role;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;

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
        // Ép sử dụng HTTPS khi chạy trên môi trường production (Railway)
        if (config('app.env') === 'production' || env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        // Register Observers
        \App\Models\Appointment::observe(\App\Observers\NotificationObserver::class);
        \App\Models\MaterialRequest::observe(\App\Observers\NotificationObserver::class);

        // Admin Super Power
        Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        // Chỉ chạy foreach nếu table roles đã tồn tại (tránh lỗi khi migrate lần đầu)
        if (!app()->runningInConsole() || \Schema::hasTable('roles')) {
            foreach (Role::permissions() as $permission) {
                Gate::define($permission, function ($user) use ($permission) {
                    return $user->hasPermission($permission);
                });
            }
        }
    }
}