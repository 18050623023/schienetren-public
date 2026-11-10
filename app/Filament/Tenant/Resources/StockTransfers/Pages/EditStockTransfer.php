<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Pages;

use App\Filament\Tenant\Resources\StockTransfers\Pages\Concerns\HandlesSerialSelection;
use App\Filament\Tenant\Resources\StockTransfers\StockTransferResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Utilities\Set;

class EditStockTransfer extends EditRecord
{
    use HandlesSerialSelection;

    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
