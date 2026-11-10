<?php

namespace App\Filament\Tenant\Pages\Dashboard;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class CsDashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;
    protected static string $routePath = 'cs';
    protected static ?string $title = 'Dashboard CS';
    protected static ?string $navigationLabel = 'Dashboard';
    // protected string $view = 'filament.tenant.pages.dashboard.index';

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole(settingTenant('tenant_cs_roles', ['customer_service']));
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Tenant\Widgets\WelcomeTenantWidget::class,
            \Filament\Widgets\AccountWidget::class,
            \App\Filament\Tenant\Widgets\DailyOperationsWidget::class,
        ];
    }
}
