<?php

namespace Tests\Unit;

use App\Filament\Tenant\Resources\StockMovements\Tables\StockMovementsTable;
use Filament\Tables\Table;
use Tests\TestCase;

class StockMovementsTableTest extends TestCase
{
    public function test_stock_movements_table_has_source_type_column(): void
    {
        $livewire = new class extends \Livewire\Component implements \Filament\Forms\Contracts\HasForms, \Filament\Tables\Contracts\HasTable {
            use \Filament\Forms\Concerns\InteractsWithForms;
            use \Filament\Tables\Concerns\InteractsWithTable;
        };

        $table = Table::make($livewire);
        $configuredTable = StockMovementsTable::configure($table);

        $columns = $configuredTable->getColumns();
        $this->assertArrayHasKey('source_type', $columns);
        $this->assertArrayHasKey('created_at', $columns);
        $this->assertArrayHasKey('branch.name', $columns);
        $this->assertArrayHasKey('product.name', $columns);
        $this->assertArrayHasKey('direction', $columns);
        $this->assertArrayHasKey('quantity', $columns);

        $filters = $configuredTable->getFilters();
        $this->assertArrayHasKey('source_type', $filters);
        $this->assertArrayHasKey('direction', $filters);
    }

    public function test_stock_movement_form_resolve_source_url(): void
    {
        // 1. StockOpname
        $m1 = new \App\Models\Tenant\StockMovement([
            'source_type' => 'App\Models\Tenant\StockOpname',
            'source_id' => 99,
        ]);
        $url1 = \App\Filament\Tenant\Resources\StockMovements\Schemas\StockMovementForm::resolveSourceUrl($m1);
        $this->assertNotNull($url1);
        $this->assertStringContainsString('stock-opnames/99', $url1);

        // 2. Transfer Ship
        $m2 = new \App\Models\Tenant\StockMovement([
            'source_type' => 'stock_transfer_ship',
            'source_id' => 50,
        ]);
        $url2 = \App\Filament\Tenant\Resources\StockMovements\Schemas\StockMovementForm::resolveSourceUrl($m2);
        $this->assertNotNull($url2);
        $this->assertStringContainsString('stock-transfers/50/edit', $url2);

        // 3. Purchase / Goods Receipt
        $m3 = new \App\Models\Tenant\StockMovement([
            'source_type' => 'purchase',
            'source_id' => 20,
        ]);
        $url3 = \App\Filament\Tenant\Resources\StockMovements\Schemas\StockMovementForm::resolveSourceUrl($m3);
        $this->assertNotNull($url3);
        $this->assertStringContainsString('goods-receipts/20', $url3);

        // 4. POS Sale (with invoice in note)
        $m4 = new \App\Models\Tenant\StockMovement([
            'source_type' => 'App\Models\Tenant\SaleItem',
            'source_id' => 12,
            'note' => 'Penjualan POS #INV-2026-999',
        ]);
        $url4 = \App\Filament\Tenant\Resources\StockMovements\Schemas\StockMovementForm::resolveSourceUrl($m4);
        $this->assertNotNull($url4);
        $this->assertStringContainsString('sales?tableSearch=INV-2026-999', $url4);
    }
}
