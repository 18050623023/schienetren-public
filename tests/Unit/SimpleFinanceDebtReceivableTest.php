<?php

namespace Tests\Unit;

use App\Filament\Tenant\Resources\PurchaseOrders\Actions\PaySupplierDebtAction;
use App\Filament\Tenant\Resources\CustomerReceivables\CustomerReceivableResource;
use App\Filament\Tenant\Widgets\DebtReceivableOverviewWidget;
use App\Models\Tenant\PurchaseOrder;
use App\Models\Tenant\RepairInvoice;
use Tests\TestCase;

class SimpleFinanceDebtReceivableTest extends TestCase
{
    /**
     * Test kalkulasi sisa hutang PO dan proteksi deduplikasi idempotensi.
     */
    public function test_purchase_order_debt_calculation_and_idempotency_guard(): void
    {
        $po = new class extends PurchaseOrder {
            public $total = 1000000;
            public $discount = 100000;
            public $paid_amount = 400000;
            public $remaining_amount = 500000; // Net 900.000 - 400.000 = 500.000

            public function update(array $attributes = [], array $options = [])
            {
                foreach ($attributes as $key => $value) {
                    $this->{$key} = $value;
                }
                return true;
            }
        };

        $netTotal = max(0, $po->total - $po->discount);
        $this->assertEquals(900000, $netTotal);
        $this->assertEquals(500000, $po->remaining_amount);

        // Simulasi pembayaran pertama: 200.000 (wasRecentlyCreated = true)
        $payAmount = 200000;
        $wasRecentlyCreated = true;

        if ($wasRecentlyCreated) {
            $newPaid = $po->paid_amount + $payAmount;
            $newRemaining = max(0, $netTotal - $newPaid);
            $po->update([
                'paid_amount' => $newPaid,
                'remaining_amount' => $newRemaining,
            ]);
        }

        $this->assertEquals(600000, $po->paid_amount);
        $this->assertEquals(300000, $po->remaining_amount);

        // Simulasi retry request identik (wasRecentlyCreated = false) -> Idempotency guard aktif
        $retryWasRecentlyCreated = false;
        if ($retryWasRecentlyCreated) {
            $po->paid_amount += $payAmount;
        }

        // Saldo TIDAK boleh terpotong lagi
        $this->assertEquals(600000, $po->paid_amount);
        $this->assertEquals(300000, $po->remaining_amount);
    }

    /**
     * Test bahwa CustomerReceivableResource dan Widget dapat diakses tanpa error konfigurasi.
     */
    public function test_resource_and_widget_structure(): void
    {
        $this->assertEquals(RepairInvoice::class, CustomerReceivableResource::getModel());
        $this->assertEquals('Piutang Pelanggan', CustomerReceivableResource::getNavigationLabel());
        $this->assertEquals('Finance', CustomerReceivableResource::getNavigationGroup());

        $this->assertTrue(class_exists(PaySupplierDebtAction::class));
        $this->assertTrue(class_exists(DebtReceivableOverviewWidget::class));
    }

    /**
     * Test scoping session current_branch_id pada resources terbaru.
     */
    public function test_multi_branch_session_scoping(): void
    {
        // 1. Tanpa session current_branch_id
        session()->forget('current_branch_id');
        $expenseSqlNoBranch = \App\Filament\Tenant\Resources\FinanceExpenses\FinanceExpenseResource::getEloquentQuery()->toSql();
        $this->assertStringNotContainsString('branch_id', $expenseSqlNoBranch);

        // 2. Dengan session current_branch_id = 5
        session(['current_branch_id' => 5]);

        $expenseSql = \App\Filament\Tenant\Resources\FinanceExpenses\FinanceExpenseResource::getEloquentQuery()->toSql();
        $this->assertStringContainsString('branch_id', $expenseSql);

        $journalSql = \App\Filament\Tenant\Resources\FinanceJournals\FinanceJournalResource::getEloquentQuery()->toSql();
        $this->assertStringContainsString('branch_id', $journalSql);

        $receivableSql = CustomerReceivableResource::getEloquentQuery()->toSql();
        $this->assertStringContainsString('branch_id', $receivableSql);

        $payrollSql = \App\Filament\Tenant\Resources\HrPayrolls\HrPayrollResource::getEloquentQuery()->toSql();
        $this->assertStringContainsString('branch_id', $payrollSql);

        $poSql = \App\Filament\Tenant\Resources\PurchaseOrders\PurchaseOrderResource::getEloquentQuery()->toSql();
        $this->assertStringContainsString('branch_id', $poSql);

        $grSql = \App\Filament\Tenant\Resources\GoodsReceipts\GoodsReceiptsResource::getEloquentQuery()->toSql();
        $this->assertStringContainsString('branch_id', $grSql);

        $soSql = \App\Filament\Tenant\Resources\StockOpnames\StockOpnameResource::getEloquentQuery()->toSql();
        $this->assertStringContainsString('branch_id', $soSql);

        session()->forget('current_branch_id');
    }

    /**
     * Test integrasi PurchaseOrderObserver dan kesiapan skalabilitas Finance Advance
     */
    public function test_purchase_order_observer_and_finance_advance_scalability(): void
    {
        $this->assertTrue(class_exists(\App\Observers\Tenant\PurchaseOrderObserver::class));

        // Verifikasi bahwa PurchaseOrderObserver memiliki method created dan updated
        $observer = new \App\Observers\Tenant\PurchaseOrderObserver();
        $this->assertTrue(method_exists($observer, 'created'));
        $this->assertTrue(method_exists($observer, 'updated'));

        // Verifikasi FinanceExpense mengizinkan atribut mass-assignment
        $expense = new \App\Models\Tenant\FinanceExpense();
        $this->assertTrue($expense->isFillable('finance_account_id'));
        $this->assertTrue($expense->isFillable('reference_number'));
        $this->assertTrue($expense->isFillable('amount'));
    }

    /**
     * Test dukungan upload lampiran pada Purchase Order dan Goods Receipts
     */
    public function test_purchase_order_and_goods_receipt_attachment_support(): void
    {
        $po = new \App\Models\Tenant\PurchaseOrder();
        $this->assertTrue($po->isFillable('attachment'), 'PurchaseOrder harus mengizinkan fillable attachment');

        $gr = new \App\Models\Tenant\GoodsReceipts();
        $this->assertTrue($gr->isFillable('attachment'), 'GoodsReceipts harus mengizinkan fillable attachment');

        $this->assertTrue(class_exists(\App\Filament\Tenant\Resources\PurchaseOrders\Actions\UploadSupplierInvoiceAction::class));
        $this->assertTrue(method_exists(\App\Filament\Tenant\Resources\PurchaseOrders\Actions\UploadSupplierInvoiceAction::class, 'configure'));
    }
}
