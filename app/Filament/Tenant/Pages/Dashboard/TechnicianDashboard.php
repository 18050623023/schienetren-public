<?php

namespace App\Filament\Tenant\Pages\Dashboard;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class TechnicianDashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static string $routePath = 'teknisi';
    protected static ?string $title = 'Dashboard Teknisi';
    protected static ?string $navigationLabel = 'Dashboard';

    // protected string $view = 'filament.tenant.pages.dashboard.index';

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole(settingTenant('tenant_technical_roles', ['teknisi', 'technician']));
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
