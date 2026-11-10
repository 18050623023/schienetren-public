<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders;

use App\Filament\Tenant\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use App\Filament\Tenant\Resources\PurchaseOrders\Pages\EditPurchaseOrder;
use App\Filament\Tenant\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Filament\Tenant\Resources\PurchaseOrders\Pages\ViewPurchaseOrder;
use App\Filament\Tenant\Resources\PurchaseOrders\RelationManagers\GoodsReceiptsRelationManager;
use App\Filament\Tenant\Resources\PurchaseOrders\RelationManagers\OneToManyRelationManager;
use App\Filament\Tenant\Resources\PurchaseOrders\Schemas\PurchaseOrderForm;
use App\Filament\Tenant\Resources\PurchaseOrders\Tables\PurchaseOrdersTable;
use App\Models\Tenant\PurchaseOrder;
use BackedEnum;
use UnitEnum;
use App\Enums\TenantFeature;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Purchase Order';
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
        return PurchaseOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            'goodsReceipts' => GoodsReceiptsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseOrders::route('/'),
            'create' => CreatePurchaseOrder::route('/create'),
            'edit' => EditPurchaseOrder::route('/{record}/edit'),
            'view' => ViewPurchaseOrder::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->when(session('current_branch_id'), fn (\Illuminate\Database\Eloquent\Builder $query, $branchId) => $query->where('branch_id', $branchId))
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}
