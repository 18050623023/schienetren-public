<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\Pages;

use App\Filament\Tenant\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }



    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Orderan'),
            'draft' => Tab::make('Orderan Baru')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', 'draft')),
            'confirm' => Tab::make('Orderan Dikonfirmasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', 'confirmed')->orWhere('status', '=', 'partially')),
            'complete' => Tab::make('Orderan Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', 'completed')),

            // 'remaining' => Tab::make('Pembelian Belum Lunas')
                // ->modifyQueryUsing(fn (Builder $query) => $query->where('remaining_amount', '!=', 0)),
            'remaining' => Tab::make('Hutang Belum Lunas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('remaining_amount', '>', 0)->where('status', '!=', 'canceled'))
                ->badge(fn () => \App\Models\Tenant\PurchaseOrder::where('remaining_amount', '>', 0)->where('status', '!=', 'canceled')->count() ?: null)
                ->badgeColor('danger'),
        ];
    }
}
