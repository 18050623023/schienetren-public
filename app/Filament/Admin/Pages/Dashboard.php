<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\UserRoleWidget;
use Filament\Pages\Dashboard as PagesDashboard;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Widgets\AccountWidget;

class Dashboard extends PagesDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    // protected string $view = 'filament.admin.pages.dashboard';

    protected function getHeaderWidgets(): array

    {
        return [
            // AccountWidget::class,
            // UserRoleWidget::class,
            // AccountWidget::class,
            // WelcomeTenantWidget::class,
            // \App\Filament\Widgets\OrdersStatsWidget::class,
        ];
    }
}
