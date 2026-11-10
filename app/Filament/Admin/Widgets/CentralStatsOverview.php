<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class CentralStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $currentMonthRevenue = Invoice::whereMonth('created_at', Carbon::now()->month)
            ->where('status', 'paid')
            ->sum('total_amount');

        return [
            Stat::make('Total Tenants', Tenant::count())
                ->description('All registered tenants')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Active Subscriptions', Subscription::where('status', 'active')->count())
                ->description('Currently active plans')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),
            Stat::make('Monthly Revenue', 'Rp ' . number_format($currentMonthRevenue, 0, ',', '.'))
                ->description('Paid invoices this month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
