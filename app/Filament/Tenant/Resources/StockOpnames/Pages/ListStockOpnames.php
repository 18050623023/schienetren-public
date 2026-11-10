<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages;

use App\Filament\Tenant\Resources\StockOpnames\StockOpnameResource;
use App\Enums\TenantFeature;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStockOpnames extends ListRecords
{
    protected static string $resource = StockOpnameResource::class;

    public function getSubNavigation(): array
    {
        return static::getResource()::getSubNavigation();
    }

    public function getSubheading(): ?string
    {
        $branchId = session('current_branch_id');
        if (! $branchId) return null;

        $hasStock = \App\Models\Tenant\Stock::where('branch_id', $branchId)->where('stock', '>', 0)->exists();
        $hasOpname = tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME);

        if ($hasStock && ! $hasOpname) {
            return 'Saldo awal cabang ini telah terkonfigurasi. Untuk melakukan audit fisik selisih stok berkala, silakan aktifkan fitur Stock Opname.';
        }

        return null;
    }

    public function getTabs(): array
    {
        $hasOpname = tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME);
        $tabs = [];

        if ($hasOpname) {
            $tabs['audit'] = Tab::make('Audit Stock Opname')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', 'regular'));
        }

        $tabs['initial'] = Tab::make('Initial Stock')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', 'initial'));

        return $tabs;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME) ? 'audit' : 'initial';
    }


    // protected function getTableQuery(): Builder
    // {
    //     $type = request()->query('type'); // initial / regular

    //     return parent::getTableQuery()
    //         ->when($type, function (Builder $query) use ($type) {
    //             $query->where('type', $type);
    //         });
    // }
}
