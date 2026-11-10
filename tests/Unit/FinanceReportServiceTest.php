<?php

namespace Tests\Unit;

use App\Models\Tenant\FinanceExpense;
use App\Models\Tenant\PurchaseOrder;
use App\Models\Tenant\RepairInvoice;
use App\Models\Tenant\RepairPayment;
use App\Services\Reports\FinanceReportService;
use Tests\TestCase;

class FinanceReportServiceTest extends TestCase
{
    /**
     * Test bahwa FinanceReportService dapat diinstansiasi dengan benar.
     */
    public function test_finance_report_service_can_be_instantiated(): void
    {
        $service = new FinanceReportService();
        $this->assertInstanceOf(FinanceReportService::class, $service);
    }

    /**
     * Test bahwa query scopes pada FinanceExpense terdefinisi dan menghasilkan query SQL yang valid.
     */
    public function test_finance_expense_query_scopes(): void
    {
        $expense = new FinanceExpense();

        $queryDates = $expense->betweenDates('2026-10-01', '2026-10-31');
        $this->assertStringContainsString('date', $queryDates->toSql());

        $queryBranch = (new FinanceExpense())->forBranch(2);
        $this->assertStringContainsString('branch_id', $queryBranch->toSql());

        $queryAccount = (new FinanceExpense())->forAccount(5);
        $this->assertStringContainsString('finance_account_id', $queryAccount->toSql());

        $queryMethod = (new FinanceExpense())->forPaymentMethod(1);
        $this->assertStringContainsString('payment_method_id', $queryMethod->toSql());
    }

    /**
     * Test bahwa query scopes pada RepairPayment terdefinisi dan valid.
     */
    public function test_repair_payment_query_scopes(): void
    {
        $payment = new RepairPayment();

        $queryDates = $payment->betweenDates('2026-10-01', '2026-10-31');
        $this->assertStringContainsString('payment_date', $queryDates->toSql());

        $queryBranch = (new RepairPayment())->forBranch(2);
        $this->assertStringContainsString('branch_id', $queryBranch->toSql());

        $queryMethod = (new RepairPayment())->forPaymentMethod(3);
        $this->assertStringContainsString('payment_method_id', $queryMethod->toSql());

        $queryType = (new RepairPayment())->type('down_payment');
        $this->assertStringContainsString('type', $queryType->toSql());
    }

    /**
     * Test bahwa query scopes pada RepairInvoice terdefinisi dan valid.
     */
    public function test_repair_invoice_query_scopes(): void
    {
        $invoice = new RepairInvoice();

        $queryDates = $invoice->betweenDates('2026-10-01', '2026-10-31');
        $this->assertStringContainsString('invoice_date', $queryDates->toSql());

        $queryBranch = (new RepairInvoice())->forBranch(2);
        $this->assertStringContainsString('branch_id', $queryBranch->toSql());

        $queryUnpaid = (new RepairInvoice())->unpaid();
        $this->assertStringContainsString('remaining_amount', $queryUnpaid->toSql());

        $queryOverdue = (new RepairInvoice())->overdue();
        $this->assertStringContainsString('due_date', $queryOverdue->toSql());
        $this->assertStringContainsString('remaining_amount', $queryOverdue->toSql());
    }

    /**
     * Test bahwa query scope unpaid pada PurchaseOrder terdefinisi.
     */
    public function test_purchase_order_unpaid_scope(): void
    {
        $po = new PurchaseOrder();

        $queryUnpaid = $po->unpaid();
        $this->assertStringContainsString('remaining_amount', $queryUnpaid->toSql());
        $this->assertStringContainsString('status', $queryUnpaid->toSql());
    }
}
