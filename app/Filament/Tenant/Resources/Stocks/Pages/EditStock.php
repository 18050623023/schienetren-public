<?php

namespace App\Filament\Tenant\Resources\Stocks\Pages;

use App\Filament\Tenant\Resources\Stocks\StockResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStock extends EditRecord
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
