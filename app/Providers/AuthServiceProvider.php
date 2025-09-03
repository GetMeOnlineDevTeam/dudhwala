<?php

namespace App\Providers;

use App\Models\User;
use App\Services\PermissionService; // if you created it
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            // Full bypass for superadmin
            if (strcasecmp($user->role ?? '', 'superadmin') === 0) {
                return true;
            }
            // Dynamic permission lookup for admins (optional, if you use PermissionService)
            return app(PermissionService::class)->check($user->role ?? '', $ability) ?: null;
        });
    }
}
