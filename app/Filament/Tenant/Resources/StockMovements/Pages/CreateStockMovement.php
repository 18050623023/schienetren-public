<?php

namespace App\Filament\Tenant\Resources\StockMovements\Pages;

use App\Filament\Tenant\Resources\StockMovements\StockMovementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;
}
