<?php

namespace App\Observers\Tenant;

use App\Models\Tenant\HrPayroll;
use App\Services\FinanceAccountingService;

class HrPayrollObserver
{
    protected FinanceAccountingService $financeService;

    public function __construct(FinanceAccountingService $financeService)
    {
        $this->financeService = $financeService;
    }

    public function updated(HrPayroll $payroll)
    {
        // Hanya trigger jika status berubah
        if ($payroll->isDirty('status')) {
            if ($payroll->status === 'paid') {
                if ($payroll->net_salary <= 0) return;

                // 1. Mode Finance Simple: Otomatis masuk ke tabel finance_expenses
                if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE)) {
                    \App\Models\Tenant\FinanceExpense::updateOrCreate(
                        [
                            'reference_number' => 'PAYROLL-' . $payroll->period . '-' . $payroll->employee_id,
                        ],
                        [
                            'branch_id' => $payroll->branch_id,
                            'date' => now()->toDateString(),
                            'amount' => $payroll->net_salary,
                            'payment_method_id' => $payroll->payment_method_id,
                            'description' => 'Pembayaran Gaji Periode ' . $payroll->period . ' - ' . ($payroll->employee->name ?? 'Karyawan'),
                            'created_by' => \Illuminate\Support\Facades\Auth::id() ?? $payroll->employee->user_id,
                        ]
                    );
                    return;
                }

                // 2. Mode Finance Advance: Masuk ke Buku Jurnal Debit/Kredit
                $branchId = $payroll->branch_id;
                $cashAccountId = settingTenant('finance_account_cash', 1);
                $salaryAccountId = settingTenant('finance_account_salary_expense', 6);

                $items = [
                    [
                        'finance_account_id' => $salaryAccountId,
                        'debit' => $payroll->net_salary,
                        'credit' => 0,
                        'description' => 'Beban Gaji ' . ($payroll->employee->name ?? 'Karyawan') . ' (' . $payroll->period . ')',
                    ],
                    [
                        'finance_account_id' => $cashAccountId,
                        'debit' => 0,
                        'credit' => $payroll->net_salary,
                        'description' => 'Pembayaran Gaji',
                    ]
                ];

                $this->financeService->createJournalEntry(
                    $branchId,
                    now()->toDateString(),
                    'Pembayaran Gaji Periode ' . $payroll->period,
                    $items,
                    $payroll
                );
            } elseif ($payroll->getOriginal('status') === 'paid') {
                // Jika status dikembalikan dari 'paid' ke 'draft' pada mode simple, batalkan pengeluaran terkait
                if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE)) {
                    \App\Models\Tenant\FinanceExpense::where('reference_number', 'PAYROLL-' . $payroll->period . '-' . $payroll->employee_id)->delete();
                }
            }
        }
    }

    public function deleted(HrPayroll $payroll)
    {
        if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE)) {
            \App\Models\Tenant\FinanceExpense::where('reference_number', 'PAYROLL-' . $payroll->period . '-' . $payroll->employee_id)->delete();
        }
    }
}
