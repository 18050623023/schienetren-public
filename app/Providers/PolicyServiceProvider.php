<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Stancl\Tenancy\Events\TenancyInitialized;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\PermissionRegistrar;

class PolicyServiceProvider extends ServiceProvider
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
        //  Gate::policy(
        //     \App\Models\Tenant\Permission::class,
        //     // \App\Policies\Tenant\PermissionPolicy::class
        // );

        // Gate::policy(
        //     \App\Models\Tenant\User::class,
        //     \App\Policies\Tenant\UserPolicy::class
        // );

    }
}
