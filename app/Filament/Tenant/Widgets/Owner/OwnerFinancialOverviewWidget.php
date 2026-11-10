<?php

namespace App\Filament\Tenant\Widgets\Owner;

use App\Enums\RepairStatus;
use App\Models\Tenant\Client;
use App\Models\Tenant\Repair;
use App\Models\Tenant\RepairInvoice;
use App\Models\Tenant\RepairPayment;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================================================
 * WIDGET: RINGKASAN KEUANGAN & PERFORMA BISNIS OWNER
 * ============================================================================
 * Widget ini menampilkan kartu indikator angka utama (KPI) bagi Owner:
 * 1. Pendapatan Bulan Ini (Total pembayaran servis masuk bulan ini & hari ini)
 * 2. Piutang Pelanggan (Sisa tagihan invoice yang belum lunas)
 * 3. Servis Aktif Berjalan (Unit perbaikan yang sedang ditangani)
 * 4. Total Pelanggan (Akumulasi pelanggan & pertumbuhan pelanggan baru)
 */
class OwnerFinancialOverviewWidget extends BaseWidget
{
    // Urutan posisi widget pada halaman
    protected static ?int $sort = 2;

    /**
     * Hak Akses:
     * Hanya role 'owner' atau role bypass tenant yang diizinkan melihat widget ini.
     */
    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(settingTenant('tenant_bypass_roles', ['owner']));
    }

    /**
     * Kalkulasi data statistik untuk setiap kartu indikator.
     */
    protected function getStats(): array
    {
        // ---------------------------------------------------------------------
        // 1. Filter Cabang Aktif (Multitenancy Branch Scoping)
        // ---------------------------------------------------------------------
        $branchId = session('current_branch_id');

        // ---------------------------------------------------------------------
        // 2. Query Pendapatan Kas Riil (RepairPayment)
        // ---------------------------------------------------------------------
        $paymentQuery = RepairPayment::query();
        if ($branchId) {
            $paymentQuery->whereHas('repairInvoice.repair', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        // Omset bulan berjalan
        $revenueThisMonth = (clone $paymentQuery)
            ->whereYear('payment_date', Carbon::now()->year)
            ->whereMonth('payment_date', Carbon::now()->month)
            ->sum('amount');

        // Omset hari ini
        $revenueToday = (clone $paymentQuery)
            ->whereDate('payment_date', Carbon::today())
            ->sum('amount');

        // ---------------------------------------------------------------------
        // 3. Query Piutang Pelanggan (Repair Invoices: unpaid / partially_paid)
        // ---------------------------------------------------------------------
        $invoiceQuery = RepairInvoice::query()->whereIn('status', ['unpaid', 'partially_paid']);
        if ($branchId) {
            $invoiceQuery->whereHas('repair', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $totalReceivables = (clone $invoiceQuery)->sum('remaining_amount');
        $unpaidInvoiceCount = (clone $invoiceQuery)->count();

        // ---------------------------------------------------------------------
        // 4. Query Servis Aktif (Belum diserahkan ke pelanggan & bukan cancel)
        // ---------------------------------------------------------------------
        $activeRepairQuery = Repair::query()
            ->whereNull('delivered_at')
            ->whereNull('canceled_at')
            ->whereNotIn('status', [RepairStatus::Cancel, RepairStatus::NotPossible]);
        if ($branchId) {
            $activeRepairQuery->where('branch_id', $branchId);
        }
        $activeRepairCount = $activeRepairQuery->count();

        // ---------------------------------------------------------------------
        // 5. Query Pelanggan / Klien (Total & Pertumbuhan Baru Bulan Ini)
        // ---------------------------------------------------------------------
        $clientQuery = Client::query();
        $totalClients = (clone $clientQuery)->count();
        $newClientsThisMonth = (clone $clientQuery)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        // ---------------------------------------------------------------------
        // 6. Return Kartu Statistik Filament
        // ---------------------------------------------------------------------
        return [
            // Kartu 1: Pendapatan Bulan Ini
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($revenueThisMonth, 0, ',', '.'))
                ->description('Hari ini: Rp ' . number_format($revenueToday, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            // Kartu 2: Piutang Pelanggan
            Stat::make('Piutang Pelanggan', 'Rp ' . number_format($totalReceivables, 0, ',', '.'))
                ->description($unpaidInvoiceCount . ' tagihan belum lunas')
                ->descriptionIcon('heroicon-m-clock')
                ->color($totalReceivables > 0 ? 'warning' : 'gray'),

            // Kartu 3: Unit Servis yang Sedang Dikerjakan
            Stat::make('Servis Aktif Berjalan', $activeRepairCount . ' Unit')
                ->description('Dalam proses penanganan')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('primary'),

            // Kartu 4: Total Pelanggan Terdaftar
            Stat::make('Total Pelanggan', number_format($totalClients, 0, ',', '.') . ' Klien')
                ->description('+' . $newClientsThisMonth . ' klien baru bulan ini')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
