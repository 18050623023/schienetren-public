<?php

namespace App\Filament\Tenant\Widgets;

use App\Enums\RepairStatus;
use App\Models\Tenant\Repair;
use App\Models\Tenant\RepairItem;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DailyOperationsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $allowedRoles = array_merge(
            (array) settingTenant('tenant_bypass_roles', ['owner']),
            (array) settingTenant('tenant_cs_roles', ['customer_service']),
            (array) settingTenant('tenant_technical_roles', ['teknisi', 'technician'])
        );

        return $user->hasRole($allowedRoles);
    }

    protected function getStats(): array
    {
        $branchId = session('current_branch_id');

        $repairQuery = Repair::query();
        if ($branchId) {
            $repairQuery->where('branch_id', $branchId);
        }

        $repairItemQuery = RepairItem::query();
        if ($branchId) {
            $repairItemQuery->whereHas('repairDevice.repair', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $barangTerjualHariIni = (clone $repairItemQuery)->whereDate('created_at', Carbon::today())->sum('qty');
        $totalBarangTerjual = (clone $repairItemQuery)->sum('qty');

        $servisMasukHariIni = (clone $repairQuery)->whereDate('created_at', Carbon::today())->count();
        $pengerjaanHariIni = (clone $repairQuery)->where('status', RepairStatus::InService)->count();
        $bisaDiambilHariIni = (clone $repairQuery)->where('status', RepairStatus::ReadyToPickup)->whereNull('delivered_at')->count();
        $sudahDiambilHariIni = (clone $repairQuery)->whereNotNull('delivered_at')->whereDate('delivered_at', Carbon::today())->count();

        return [
            Stat::make('Total Barang Terjual', number_format($totalBarangTerjual, 0, ',', '.') . ' Item')
                ->description('Hari ini: +' . number_format($barangTerjualHariIni, 0, ',', '.') . ' item')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
            Stat::make('Servis Masuk Hari Ini', $servisMasukHariIni)
                ->description('Unit diterima hari ini')
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color('primary'),
            Stat::make('Pengerjaan Hari Ini', $pengerjaanHariIni)
                ->description('Unit dalam proses servis')
                ->descriptionIcon('heroicon-m-wrench')
                ->color('warning'),
            Stat::make('Bisa Diambil', $bisaDiambilHariIni)
                ->description('Siap diserahkan ke pelanggan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
            Stat::make('Sudah Diambil Hari Ini', $sudahDiambilHariIni)
                ->description('Unit diserahkan hari ini')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('success'),
        ];
    }
}
