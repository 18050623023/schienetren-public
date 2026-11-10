<?php

namespace App\Filament\Tenant\Resources\GoodsReceipts;

use App\Filament\Tenant\Resources\GoodsReceipts\Pages\CreateGoodsReceipts;
use App\Filament\Tenant\Resources\GoodsReceipts\Pages\EditGoodsReceipts;
use App\Filament\Tenant\Resources\GoodsReceipts\Pages\ListGoodsReceipts;
use App\Filament\Tenant\Resources\GoodsReceipts\Pages\ViewGoodsReceipt;
use App\Filament\Tenant\Resources\GoodsReceipts\Schemas\GoodsReceiptsForm;
use App\Filament\Tenant\Resources\GoodsReceipts\Tables\GoodsReceiptsTable;
use App\Models\Tenant\GoodsReceipts;
use BackedEnum;
use UnitEnum;
use App\Enums\TenantFeature;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GoodsReceiptsResource extends Resource
{
    protected static ?string $model = GoodsReceipts::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Goods Receipts';
    protected static string|UnitEnum|null $navigationGroup = 'Purchasing';

    public static function canViewAny(): bool
    {
        return tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER);
    }

    public static function canCreate(): bool
    {
        return tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER) && parent::canCreate();
    }

    public static function form(Schema $schema): Schema
    {
        return GoodsReceiptsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GoodsReceiptsTable::configure($table);
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
            'index' => ListGoodsReceipts::route('/'),
            'create' => CreateGoodsReceipts::route('/create'),
            'edit' => EditGoodsReceipts::route('/{record}/edit'),
            'view' => ViewGoodsReceipt::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->when(session('current_branch_id'), fn (\Illuminate\Database\Eloquent\Builder $query, $branchId) => 
                $query->whereHas('purchaseOrder', fn ($q) => $q->where('branch_id', $branchId))
            );
    }
}
