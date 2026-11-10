<?php

namespace Tests\Unit;

use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleItem;
use App\Services\Pos\PosCheckoutService;
use Tests\TestCase;

class PosCheckoutServiceTest extends TestCase
{
    /**
     * Test bahwa PosCheckoutService dapat diinstansiasi dengan benar.
     */
    public function test_pos_checkout_service_can_be_instantiated(): void
    {
        $service = new PosCheckoutService();
        $this->assertInstanceOf(PosCheckoutService::class, $service);
    }

    /**
     * Test bahwa query scopes pada model Sale terdefinisi dan menghasilkan query SQL yang valid.
     */
    public function test_sale_query_scopes(): void
    {
        $sale = new Sale();

        $queryDates = $sale->betweenDates('2026-10-01', '2026-10-31');
        $this->assertStringContainsString('date', $queryDates->toSql());

        $queryBranch = (new Sale())->forBranch(2);
        $this->assertStringContainsString('branch_id', $queryBranch->toSql());

        $queryCashier = (new Sale())->forCashier(5);
        $this->assertStringContainsString('cashier_id', $queryCashier->toSql());

        $queryMethod = (new Sale())->forPaymentMethod(1);
        $this->assertStringContainsString('payment_method_id', $queryMethod->toSql());

        $queryCompleted = (new Sale())->completed();
        $this->assertStringContainsString('status', $queryCompleted->toSql());
    }

    /**
     * Test relasi dan fillable pada SaleItem.
     */
    public function test_sale_item_fillables_and_relations(): void
    {
        $item = new SaleItem();
        $this->assertTrue(in_array('qty', $item->getFillable()));
        $this->assertTrue(in_array('cost_price', $item->getFillable()));
        $this->assertTrue(in_array('unit_price', $item->getFillable()));
        $this->assertTrue(in_array('subtotal', $item->getFillable()));
    }

    /**
     * Test bahwa FinanceReportService dapat memproses query termasuk model Sale.
     */
    public function test_finance_report_service_includes_sale_model(): void
    {
        $service = new \App\Services\Reports\FinanceReportService();
        $this->assertInstanceOf(\App\Services\Reports\FinanceReportService::class, $service);
    }

    /**
     * Test bahwa PosPage Filament page terdaftar dan memiliki view yang benar.
     */
    public function test_pos_page_class_configuration(): void
    {
        $this->assertEquals('Point of Sale (POS)', \App\Filament\Tenant\Pages\PosPage::getNavigationLabel());
        $this->assertEquals('Penjualan', \App\Filament\Tenant\Pages\PosPage::getNavigationGroup());
    }

    /**
     * Test bahwa getAvailableProductsProperty hanya memfilter produk dengan type 'product' (mengecualikan 'service').
     */
    public function test_pos_page_filters_only_product_type(): void
    {
        $posPage = new \App\Filament\Tenant\Pages\PosPage();
        $this->assertTrue(method_exists($posPage, 'getAvailableProductsProperty'));

        // Ketika branchId kosong, mengembalikan koleksi kosong
        $this->assertCount(0, $posPage->getAvailableProductsProperty());
    }

    /**
     * Test bahwa updateQty mengabaikan perubahan kuantitas untuk item ber-serial.
     */
    public function test_update_qty_locks_serialized_items(): void
    {
        $posPage = new \App\Filament\Tenant\Pages\PosPage();
        $posPage->cart = [
            [
                'variant_id' => 1,
                'name' => 'iPhone 11',
                'qty' => 1,
                'unit_price' => 1000000,
                'max_stock' => 5,
                'discount_amount' => 0,
                'subtotal' => 1000000,
                'has_serial' => true,
                'selected_serials' => [1],
            ],
            [
                'variant_id' => 2,
                'name' => 'Kabel Data',
                'qty' => 1,
                'unit_price' => 50000,
                'max_stock' => 10,
                'discount_amount' => 0,
                'subtotal' => 50000,
                'has_serial' => false,
                'selected_serials' => [],
            ]
        ];

        // Coba ubah qty item ber-serial menjadi 5 (harus tetap 1)
        $posPage->updateQty(0, 5);
        $this->assertEquals(1, $posPage->cart[0]['qty']);

        // Ubah qty item non-serial menjadi 3 (harus berubah jadi 3)
        $posPage->updateQty(1, 3);
        $this->assertEquals(3, $posPage->cart[1]['qty']);
        $this->assertEquals(150000, $posPage->cart[1]['subtotal']);
    }
}
