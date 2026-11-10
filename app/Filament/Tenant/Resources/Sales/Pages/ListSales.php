<?php

namespace App\Filament\Tenant\Resources\Sales\Pages;

use App\Filament\Tenant\Pages\PosPage;
use App\Filament\Tenant\Resources\Sales\SaleResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_pos')
                ->label('Buka Kasir POS')
                ->icon(Heroicon::OutlinedShoppingCart)
                ->color('amber')
                ->url(PosPage::getUrl()),
        ];
    }
}
