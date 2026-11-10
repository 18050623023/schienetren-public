<?php
namespace App\Tenancy;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Illuminate\Support\Facades\Config;

class PermissionsBootstrapper implements TenancyBootstrapper
{
    public function bootstrap($tenant)
    {
        Config::set('permission.models.permission', \App\Models\Tenant\Permission::class);
        Config::set('permission.models.role', \App\Models\Tenant\Role::class);
        Config::set('permission.models.user', \App\Models\Tenant\User::class);

        Config::set('permission.defaults.guard', 'tenant');
    }

    public function revert()
    {
        // Kembalikan ke config normal jika tenancy non-aktif
        Config::set('permission.models.permission', \Spatie\Permission\Models\Permission::class);
        Config::set('permission.models.role', \Spatie\Permission\Models\Role::class);
        Config::set('permission.models.user', \App\Models\User::class);

        Config::set('permission.defaults.guard', 'web');
    }
}
