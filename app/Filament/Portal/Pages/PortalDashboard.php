<?php

namespace App\Filament\Portal\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use App\Filament\Portal\Pages\Billing;

class PortalDashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;
    protected static ?string $title = 'Portal Customer';

    public function getTitle(): string|Htmlable
    {
        return 'Selamat Datang, ' . Auth::user()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('go_to_billing')
                ->label('Beli Paket / Top Up')
                ->icon('heroicon-o-credit-card')
                ->color('primary')
                ->url(fn () => Billing::getUrl()),
        ];
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Portal\Widgets\CustomerOverviewWidget::class,
            \App\Filament\Portal\Widgets\CustomerRecentInvoicesWidget::class,
        ];
    }
}
