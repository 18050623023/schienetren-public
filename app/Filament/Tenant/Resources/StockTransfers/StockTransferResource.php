<?php

namespace App\Filament\Tenant\Resources\StockTransfers;

use App\Filament\Tenant\Resources\StockTransfers\Pages\CreateStockTransfer;
use App\Filament\Tenant\Resources\StockTransfers\Pages\EditStockTransfer;
use App\Filament\Tenant\Resources\StockTransfers\Pages\ListStockTransfers;
use App\Filament\Tenant\Resources\StockTransfers\Schemas\StockTransferForm;
use App\Filament\Tenant\Resources\StockTransfers\Tables\StockTransfersTable;
use App\Enums\TenantFeature;
use App\Models\Tenant\Branch;
use App\Models\Tenant\StockTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Stock Transfer';
    protected static string|UnitEnum|null $navigationGroup = 'Stock';

    public static function canViewAny(): bool
    {
        return tenant()->hasFeatureCode(TenantFeature::STOCK_TRANSFER);
    }

    public static function canCreate(): bool
    {
        return tenant()->hasFeatureCode(TenantFeature::STOCK_TRANSFER)
            && Branch::where('is_active', true)->count() > 1
            && parent::canCreate();
    }


    public static function form(Schema $schema): Schema
    {
        return StockTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockTransfersTable::configure($table);
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
            'index' => ListStockTransfers::route('/'),
            'create' => CreateStockTransfer::route('/create'),
            'edit' => EditStockTransfer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
