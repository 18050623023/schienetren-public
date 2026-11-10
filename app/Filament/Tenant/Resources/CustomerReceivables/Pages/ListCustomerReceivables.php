<?php

namespace App\Filament\Tenant\Resources\CustomerReceivables\Pages;

use App\Filament\Tenant\Resources\CustomerReceivables\CustomerReceivableResource;
use App\Models\Tenant\RepairInvoice;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCustomerReceivables extends ListRecords
{
    protected static string $resource = CustomerReceivableResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Piutang')
                ->badge(fn () => RepairInvoice::where('remaining_amount', '>', 0)->where('status', '!=', 'refunded')->count() ?: null)
                ->badgeColor('warning'),

            'overdue' => Tab::make('Jatuh Tempo (Overdue)')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereNotNull('due_date')->where('due_date', '<', now()->toDateString())
                )
                ->badge(fn () => RepairInvoice::where('remaining_amount', '>', 0)
                    ->where('status', '!=', 'refunded')
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', now()->toDateString())
                    ->count() ?: null
                )
                ->badgeColor('danger'),

            'partially' => Tab::make('Cicilan / Parsial')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'partially_paid')
                ),
        ];
    }
}
