<?php

namespace App\Filament\Tenant\Resources\Stocks;

use App\Filament\Tenant\Resources\Stocks\Pages\CreateStock;
use App\Filament\Tenant\Resources\Stocks\Pages\EditStock;
use App\Filament\Tenant\Resources\Stocks\Pages\ListStocks;
use App\Filament\Tenant\Resources\Stocks\Schemas\StockForm;
use App\Filament\Tenant\Resources\Stocks\Tables\StocksTable;
use App\Models\Tenant\Stock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Stock';
    // protected static ?string $cluster = StocksCluster::class;    
    protected static string|UnitEnum|null $navigationGroup = 'Stock';


    public static function form(Schema $schema): Schema
    {
        return StockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StocksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStocks::route('/'),
            'create' => CreateStock::route('/create'),
            'edit' => EditStock::route('/{record}/edit'),
            // 'viewstock' => ViewStock::route('/{record}/view-stock'),
        ];
    }
}
