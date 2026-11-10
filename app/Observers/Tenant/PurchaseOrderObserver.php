<?php

namespace App\Observers\Tenant;

use App\Models\Tenant\FinanceExpense;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\PurchaseOrder;

class PurchaseOrderObserver
{
    /**
     * Handle the PurchaseOrder "created" event.
     */
    public function created(PurchaseOrder $purchaseOrder): void
    {
        $this->syncDownPaymentExpense($purchaseOrder);
    }

    /**
     * Handle the PurchaseOrder "updated" event.
     */
    public function updated(PurchaseOrder $purchaseOrder): void
    {
        // 1. Jika PO dibatalkan (canceled), batalkan / hapus pengeluaran kas DP-nya
        if ($purchaseOrder->isDirty('status') && $purchaseOrder->status === 'canceled') {
            $prefix = rtrim(settingTenant('po_dp_prefix', 'PO-DP'), '-');
            FinanceExpense::where('reference_number', $prefix . '-' . $purchaseOrder->code)
                ->orWhere('reference_number', 'PO-DP-' . $purchaseOrder->code)
                ->delete();
            return;
        }

        // 2. Jika ada perubahan nominal paid_amount pada saat status masih draft
        if ($purchaseOrder->isDirty('paid_amount') && $purchaseOrder->status === 'draft') {
            $this->syncDownPaymentExpense($purchaseOrder);
        }
    }

    /**
     * Sinkronisasi pencatatan pengeluaran kas DP ke tabel finance_expenses.
     * Dirancang scalable: menyimpan finance_account_id (AP) sehingga jika mode Finance Advance
     * diaktifkan, FinanceExpenseObserver otomatis menjurnal ke Akun Hutang Usaha (Debit) & Kas (Kredit).
     */
    protected function syncDownPaymentExpense(PurchaseOrder $po): void
    {
        $dpAmount = (float) $po->paid_amount;
        $prefix = rtrim(settingTenant('po_dp_prefix', 'PO-DP'), '-');
        $refNumber = $prefix . '-' . $po->code;

        if ($dpAmount <= 0) {
            FinanceExpense::where('reference_number', $refNumber)->delete();
            return;
        }

        // Cari payment method id yang sesuai dengan enum payment_method PO
        $paymentMethodId = PaymentMethod::where('type', $po->payment_method)
            ->orWhere('name', 'like', "%{$po->payment_method}%")
            ->value('id') ?? PaymentMethod::first()?->id ?? 1;

        // Akun Hutang Usaha (AP) sebagai akun penyeimbang (scalable untuk Finance Advance)
        $apAccountId = settingTenant('finance_account_ap', 5);

        FinanceExpense::updateOrCreate(
            [
                'reference_number' => $refNumber,
            ],
            [
                'branch_id' => $po->branch_id,
                'finance_account_id' => $apAccountId,
                'payment_method_id' => $paymentMethodId,
                'date' => $po->date ?? now()->toDateString(),
                'amount' => $dpAmount,
                'description' => 'Uang Muka (DP) PO ' . $po->code . ' (' . ($po->supplier->name ?? 'Supplier') . ')',
                'created_by' => $po->created_by,
            ]
        );
    }
}
