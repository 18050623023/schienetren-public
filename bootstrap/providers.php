<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\PortalPanelProvider::class,
    App\Providers\Filament\TenantPanelProvider::class,

    App\Providers\PolicyServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,
];
