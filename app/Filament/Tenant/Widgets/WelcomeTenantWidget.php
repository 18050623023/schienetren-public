<?php

namespace App\Filament\Tenant\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Widget;

class WelcomeTenantWidget extends Widget
{

    // protected static ?int $sort = 0;
    // protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.tenant.widgets.welcome-tenant-widget';

    // protected function getStats(): array
    // {
    //     $tenant = tenancy()->tenant;
    //     return [
    //         Stat::make('Welcome to Tenant', $tenant?->business_name ?? 'Unknown')
    //             ->description('ID: ' . ($tenant?->id ?? '-')),
    //     ];
    // }
}
