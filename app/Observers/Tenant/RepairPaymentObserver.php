<?php

namespace App\Observers\Tenant;

use App\Models\Tenant\RepairPayment;
use App\Services\FinanceAccountingService;

class RepairPaymentObserver
{
    protected FinanceAccountingService $financeService;

    public function __construct(FinanceAccountingService $financeService)
    {
        $this->financeService = $financeService;
    }

    public function created(RepairPayment $repairPayment)
    {
        // 1. Sinkronkan total pembayaran dan status invoice
        $repairPayment->repairInvoice?->recalculateTotals();

        $branchId = $repairPayment->repairInvoice->repair->branch_id ?? session('current_branch_id');

        // 2. Penanganan Refund Uang Customer
        if ($repairPayment->type === 'refund') {
            // Mode Finance Simple: Otomatis catat pengeluaran kas toko
            if ($branchId) {
                // \App\Models\Tenant\FinanceExpense::create([
                \App\Models\Tenant\FinanceExpense::updateOrCreate(
                    [
                        'reference_number' => 'REFUND-' . ($repairPayment->reference_number ?? $repairPayment->id),
                    ],
                    [
                        'branch_id' => $branchId,

                        // 'expense_date' => $repairPayment->payment_date ? \Carbon\Carbon::parse($repairPayment->payment_date)->toDateString() : now()->toDateString(),
                        'date' => $repairPayment->payment_date ? \Carbon\Carbon::parse($repairPayment->payment_date)->toDateString() : now()->toDateString(),
                        'payment_method_id' => $repairPayment->payment_method_id,
                        'amount' => $repairPayment->amount,
                        'description' => 'Beban Retur & Refund Servis ' . ($repairPayment->repairInvoice->invoice_number ?? '') . ($repairPayment->refund_reason ? ' (' . $repairPayment->refund_reason . ')' : ''),
                        'created_by' => $repairPayment->created_by,
                    ]
                );
            }

            // Mode Finance Advance: (ponytail: upgrade path for double-entry journal)
            if (tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE)) {
                // Siap diimplementasikan saat modul Finance Advance diuji penuh
            }

            return;
        }

        // 3. Penanganan Pembayaran Masuk Normal
        if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE)) {
            return;
        }

        // Lacak Branch ID dari relasi: RepairPayment -> RepairInvoice -> Repair -> branch_id
        // $branchId = $repairPayment->repairInvoice->repair->branch_id ?? null;

        if (!$branchId) return;

        // Ambil pemetaan akun dari setting. Jika belum ada, gunakan fallback ID sementara.
        $cashAccountId = settingTenant('finance_account_cash', 1);
        $revenueAccountId = settingTenant('finance_account_revenue_repair', 4);

        $items = [
            [
                'finance_account_id' => $cashAccountId,
                'debit' => $repairPayment->amount,
                'credit' => 0,
                'description' => 'Pembayaran Servis ' . ($repairPayment->repairInvoice->invoice_number ?? ''),
            ],
            [
                'finance_account_id' => $revenueAccountId,
                'debit' => 0,
                'credit' => $repairPayment->amount,
                'description' => 'Pendapatan Jasa Servis',
            ]
        ];

        $this->financeService->createJournalEntry(
            $branchId,
            $repairPayment->payment_date ?? now()->toDateString(),
            'Pembayaran Invoice ' . ($repairPayment->repairInvoice->invoice_number ?? ''),
            $items,
            $repairPayment,
            $repairPayment->created_by
        );
    }

    public function deleted(RepairPayment $repairPayment)
    {
        $repairPayment->repairInvoice?->recalculateTotals();
    }
}
