<?php

namespace App\Filament\Tenant\Resources\GoodsReceipts\Pages;

use App\Filament\Tenant\Resources\GoodsReceipts\GoodsReceiptsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGoodsReceipts extends ListRecords
{
    protected static string $resource = GoodsReceiptsResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         CreateAction::make(),
    //     ];
    // }


}
