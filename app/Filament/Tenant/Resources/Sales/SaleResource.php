<?php

namespace App\Filament\Tenant\Resources\Sales;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\Sales\Pages\ListSales;
use App\Filament\Tenant\Resources\Sales\Tables\SalesTable;
use App\Models\Tenant\Sale;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;
    protected static ?string $recordTitleAttribute = 'invoice_number';
    protected static ?string $navigationLabel = 'Riwayat Penjualan';
    protected static string|UnitEnum|null $navigationGroup = 'Penjualan';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return tenant()->hasFeatureCode(TenantFeature::POS);
    }

    public static function canCreate(): bool
    {
        return false; // Transaksi dibuat melalui antarmuka Kasir POS
    }

    public static function table(Table $table): Table
    {
        return SalesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSales::route('/'),
        ];
    }
}
