<?php

namespace App\Filament\Tenant\Resources\StockMovements\Pages;

use App\Filament\Tenant\Resources\StockMovements\StockMovementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStockMovement extends EditRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
