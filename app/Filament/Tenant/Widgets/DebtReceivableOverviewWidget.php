<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\Tenant\FinanceExpense;
use App\Models\Tenant\PurchaseOrder;
use App\Models\Tenant\RepairInvoice;
use App\Models\Tenant\RepairPayment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DebtReceivableOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']));
    }

    protected function getStats(): array
    {
        $branchId = session('current_branch_id');

        // 1. Data Piutang Pelanggan
        $activeInvoices = RepairInvoice::where('remaining_amount', '>', 0)
            ->where('status', '!=', 'refunded');

        if ($branchId) {
            $activeInvoices->whereHas('repair', fn ($q) => $q->where('branch_id', $branchId));
        }

        $totalReceivable = (float) (clone $activeInvoices)->sum('remaining_amount');
        $unpaidInvoiceCount = (clone $activeInvoices)->count();

        // 2. Piutang Overdue
        $overdueInvoices = (clone $activeInvoices)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString());
        $overdueReceivable = (float) (clone $overdueInvoices)->sum('remaining_amount');
        $overdueCount = (clone $overdueInvoices)->count();

        // 3. Data Hutang Supplier
        $activePOs = PurchaseOrder::where('remaining_amount', '>', 0)
            ->where('status', '!=', 'canceled');

        if ($branchId) {
            $activePOs->where('branch_id', $branchId);
        }

        $totalSupplierDebt = (float) (clone $activePOs)->sum('remaining_amount');
        $unpaidPoCount = (clone $activePOs)->count();

        // 4. Kas Bersih Sederhana (Total Pembayaran Servis - Total Pengeluaran)

        // $totalCashIn = (float) RepairPayment::where(fn ($q) => $q->whereNull('type')->orWhere('type', '!=', 'refund'))->sum('amount');
        // $totalRefund = (float) RepairPayment::where('type', 'refund')->sum('amount');
        // $totalExpense = (float) FinanceExpense::sum('amount');
        
        $paymentQuery = RepairPayment::query();
        if ($branchId) {
            $paymentQuery->whereHas('repairInvoice.repair', fn ($q) => $q->where('branch_id', $branchId));
        }

        $totalCashIn = (float) (clone $paymentQuery)->where(fn ($q) => $q->whereNull('type')->orWhere('type', '!=', 'refund'))->sum('amount');
        $totalRefund = (float) (clone $paymentQuery)->where('type', 'refund')->sum('amount');

        $expenseQuery = FinanceExpense::query();
        if ($branchId) {
            $expenseQuery->where('branch_id', $branchId);
        }
        $totalExpense = (float) $expenseQuery->sum('amount');
        $netOperatingCash = max(0, ($totalCashIn - $totalRefund) - $totalExpense);

        return [
            Stat::make('Total Piutang Pelanggan', 'Rp ' . number_format($totalReceivable, 0, ',', '.'))
                ->description($unpaidInvoiceCount . ' invoice belum dilunasi')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger')
                ->icon('heroicon-o-document-currency-dollar'),

            Stat::make('Piutang Jatuh Tempo', 'Rp ' . number_format($overdueReceivable, 0, ',', '.'))
                ->description($overdueCount . ' invoice melewati jatuh tempo')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueCount > 0 ? 'danger' : 'gray')
                ->icon('heroicon-o-clock'),

            Stat::make('Total Hutang ke Supplier', 'Rp ' . number_format($totalSupplierDebt, 0, ',', '.'))
                ->description($unpaidPoCount . ' PO pembelian tempo')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning')
                ->icon('heroicon-o-credit-card'),

            Stat::make('Kas Bersih Toko (Est.)', 'Rp ' . number_format($netOperatingCash, 0, ',', '.'))
                ->description('Pemasukan riil servis - Beban kas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->icon('heroicon-o-wallet'),
        ];
    }
}
