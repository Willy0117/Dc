<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        Inertia::share([
            'auth' => [
                'admin' => function () {
                    $admin = auth('admin')->user();

                    return $admin ? [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'roles' => $admin->tenantRoles->pluck('name')->toArray(),
                        'permissions' => $admin->tenantPermissions()->pluck('name')->toArray(),
                    ] : null;
                },

                'user' => function () {
                    $user = auth('web')->user();

                    return $user ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'roles' => $user->tenantRoles->pluck('name')->toArray(),
                        'permissions' => $user->tenantPermissions()->pluck('name')->toArray(),
                    ] : null;
                },
            ],
        ]);
    }
}
