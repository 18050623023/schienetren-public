<?php

namespace Tests\Unit;

use App\Models\Tenant\PurchaseOrder;
use App\Models\Tenant\Stock;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\StockOpname;
use App\Models\Tenant\StockTransfer;
use App\Services\Reports\InventoryReportService;
use Tests\TestCase;

class InventoryReportServiceTest extends TestCase
{
    /**
     * Test bahwa InventoryReportService dapat diinstansiasi dengan benar.
     */
    public function test_inventory_report_service_can_be_instantiated(): void
    {
        $service = new InventoryReportService();
        $this->assertInstanceOf(InventoryReportService::class, $service);
    }

    /**
     * Test bahwa query scopes pada Stock terdefinisi dan menghasilkan query SQL yang valid.
     */
    public function test_stock_query_scopes(): void
    {
        $stock = new Stock();

        $queryBranch = $stock->forBranch(1);
        $this->assertStringContainsString('branch_id', $queryBranch->toSql());

        $queryInStock = $stock->inStock();
        $this->assertStringContainsString('stock', $queryInStock->toSql());

        $queryLowStock = $stock->lowStock(5);
        $this->assertStringContainsString('stock', $queryLowStock->toSql());

        $queryProduct = $stock->forProduct(10);
        $this->assertStringContainsString('product_id', $queryProduct->toSql());
    }

    /**
     * Test bahwa query scopes pada StockMovement terdefinisi.
     */
    public function test_stock_movement_query_scopes(): void
    {
        $movement = new StockMovement();

        $queryDates = $movement->betweenDates('2026-10-01', '2026-10-31');
        $this->assertStringContainsString('between', strtolower($queryDates->toSql()));

        $queryBranch = $movement->forBranch(1);
        $this->assertStringContainsString('branch_id', $queryBranch->toSql());

        $queryDirection = $movement->direction('in');
        $this->assertStringContainsString('direction', $queryDirection->toSql());

        $queryProduct = $movement->forProduct(5);
        $this->assertStringContainsString('product_id', $queryProduct->toSql());
    }

    /**
     * Test bahwa query scopes pada StockOpname terdefinisi.
     */
    public function test_stock_opname_query_scopes(): void
    {
        $opname = new StockOpname();

        $queryDates = $opname->betweenDates('2026-10-01', '2026-10-31');
        $this->assertStringContainsString('between', strtolower($queryDates->toSql()));

        $queryBranch = $opname->forBranch(1);
        $this->assertStringContainsString('branch_id', $queryBranch->toSql());

        $queryStatus = $opname->status('posted');
        $this->assertStringContainsString('status', $queryStatus->toSql());
    }

    /**
     * Test bahwa query scopes pada StockTransfer terdefinisi.
     */
    public function test_stock_transfer_query_scopes(): void
    {
        $transfer = new StockTransfer();

        $queryDates = $transfer->betweenDates('2026-10-01', '2026-10-31');
        $this->assertStringContainsString('between', strtolower($queryDates->toSql()));

        $queryBranch = $transfer->forBranch(2);
        $sql = $transfer->forBranch(2)->toSql();
        $this->assertStringContainsString('from_branch_id', $sql);
        $this->assertStringContainsString('to_branch_id', $sql);

        $queryStatus = $transfer->status('in_transit');
        $this->assertStringContainsString('status', $queryStatus->toSql());
    }

    /**
     * Test bahwa query scopes pada PurchaseOrder terdefinisi.
     */
    public function test_purchase_order_query_scopes(): void
    {
        $po = new PurchaseOrder();

        $queryDates = $po->betweenDates('2026-10-01', '2026-10-31');
        $this->assertStringContainsString('between', strtolower($queryDates->toSql()));

        $queryBranch = $po->forBranch(1);
        $this->assertStringContainsString('branch_id', $queryBranch->toSql());

        $querySupplier = $po->forSupplier(3);
        $this->assertStringContainsString('supplier_id', $querySupplier->toSql());

        $queryStatus = $po->status('received');
        $this->assertStringContainsString('status', $queryStatus->toSql());
    }
}
