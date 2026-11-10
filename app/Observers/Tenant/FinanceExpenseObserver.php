<?php

namespace App\Observers\Tenant;

use App\Models\Tenant\FinanceExpense;
use App\Services\FinanceAccountingService;

class FinanceExpenseObserver
{
    protected FinanceAccountingService $financeService;

    public function __construct(FinanceAccountingService $financeService)
    {
        $this->financeService = $financeService;
    }

    public function created(FinanceExpense $expense)
    {
        if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE)) {
            return;
        }
        $branchId = $expense->branch_id;
        $cashAccountId = settingTenant('finance_account_cash', 1);

        $items = [
            [
                'finance_account_id' => $expense->finance_account_id ?: settingTenant('finance_account_general_expense', 7), // Beban operasional sesuai pilihan user / fallback umum
                'debit' => $expense->amount,
                'credit' => 0,
                'description' => $expense->description,
            ],
            [
                'finance_account_id' => $cashAccountId,
                'debit' => 0,
                'credit' => $expense->amount,
                'description' => 'Pembayaran Pengeluaran',
            ]
        ];

        $this->financeService->createJournalEntry(
            $branchId,
            $expense->date,
            'Pengeluaran: ' . $expense->description,
            $items,
            $expense,
            $expense->created_by
        );
    }
}
