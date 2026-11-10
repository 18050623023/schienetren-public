<?php

namespace App\Filament\Tenant\Resources\StockOpnames;

use App\Filament\Clusters\Stocks\StocksCluster;
use App\Filament\Tenant\Resources\StockOpnames\Pages\CreateStockOpname;
use App\Filament\Tenant\Resources\StockOpnames\Pages\EditStockOpname;
use App\Filament\Tenant\Resources\StockOpnames\Pages\ListStockOpnames;
use App\Filament\Tenant\Resources\StockOpnames\Pages\ViewInitialStock;
use App\Filament\Tenant\Resources\StockOpnames\Schemas\StockOpnameForm;
use App\Filament\Tenant\Resources\StockOpnames\Tables\StockOpnamesTable;
use App\Models\Tenant\StockOpname;
use BackedEnum;
use UnitEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use App\Enums\TenantFeature;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Stock;
use Filament\Tables\Table;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockOpname::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Stock Opname';
    protected static string|UnitEnum|null $navigationGroup = 'Stock';
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    public static function getNavigationLabel(): string
    {
        return tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME)
            ? 'Stock Opname'
            : 'Initial Stock';
    }

    public static function canCreate(): bool
    {
        $branchId = session('current_branch_id');
        if ($branchId) {
            $hasStock = Stock::where('branch_id', $branchId)->where('stock', '>', 0)->exists();
            $alreadyInitial = StockOpname::where('branch_id', $branchId)->where('type', 'initial')->exists();
            if ($hasStock || $alreadyInitial) {
                return false;
            }
        } else {
            $anyEligible = Branch::where('is_active', true)
                ->whereDoesntHave('stockOpnames', fn ($q) => $q->where('type', 'initial'))
                ->whereDoesntHave('stocks', fn ($q) => $q->where('stock', '>', 0))
                ->exists();
            if (! $anyEligible) {
                return false;
            }
        }

        return parent::canCreate();
    }

    public static function form(Schema $schema): Schema
    {
        return StockOpnameForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockOpnamesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockOpnames::route('/'),
            'audit'  => Pages\Audit\AuditStockOpname::route('/audit'),
            'create' => CreateStockOpname::route('/create'),
            'view' => ViewInitialStock::route('/{record}'),
            // 'edit' => EditStockOpname::route('/{record}/edit'),
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->icon(static::getNavigationIcon() ?? Heroicon::OutlinedRectangleStack)
                ->group(static::getNavigationGroup())
                ->url(static::getUrl('index'))
                ->isActiveWhen(fn () =>
                    request()->routeIs('filament.*.resources.stock-opnames.*')
                ),
        ];
    }

    public static function getSubNavigation(): array
    {
        return [
            NavigationItem::make('Daftar Stock Opname')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->url(static::getUrl('index'))
                ->isActiveWhen(fn () =>
                    request()->routeIs('filament.*.resources.stock-opnames.index')
                ),

            NavigationItem::make('Stock Opname (Audit)')
                ->icon('heroicon-o-clipboard-document-check')
                ->url(static::getUrl('audit'))
                ->visible(fn () => tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME))
                ->isActiveWhen(fn () =>
                    request()->routeIs('filament.*.resources.stock-opnames.audit')
                ),

            NavigationItem::make('Initial Stock (Cabang Baru)')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->url(static::getUrl('create'))
                ->visible(function () {
                    $branchId = session('current_branch_id');
                    if ($branchId) {
                        $hasStock = Stock::where('branch_id', $branchId)->where('stock', '>', 0)->exists();
                        $alreadyInitial = StockOpname::where('branch_id', $branchId)->where('type', 'initial')->exists();
                        return ! $hasStock && ! $alreadyInitial;
                    }

                    return Branch::where('is_active', true)
                        ->whereDoesntHave('stockOpnames', fn ($q) => $q->where('type', 'initial'))
                        ->whereDoesntHave('stocks', fn ($q) => $q->where('stock', '>', 0))
                        ->exists();
                })
                ->isActiveWhen(fn () =>
                    request()->routeIs('filament.*.resources.stock-opnames.create')
                ),
        ];
    }

    // public static function canViewAny(): bool
    // {
    //     // return Auth::user()->can('view stock opname');
    //     return true;
    // }

    // public static function getNavigationItems(): array
    // {
    //     return [
    //         NavigationItem::make('Initial Stock')
    //             ->icon(Heroicon::OutlinedRectangleStack)
    //             ->group('Stock Setting')
    //             ->url(static::getUrl('index', ['type' => 'initial'])),

    //         NavigationItem::make('Stock Audit')
    //             ->icon(Heroicon::OutlinedRectangleStack)
    //             ->group('Stock Setting')
    //             ->url(static::getUrl('index', ['type' => 'regular'])),
    //     ];
    // }


    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->when(session('current_branch_id'), fn (\Illuminate\Database\Eloquent\Builder $query, $branchId) => $query->where('branch_id', $branchId))
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}
