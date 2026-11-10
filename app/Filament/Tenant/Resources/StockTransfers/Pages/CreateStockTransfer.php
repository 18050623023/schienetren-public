<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Pages;

use App\Filament\Tenant\Resources\StockTransfers\Pages\Concerns\HandlesSerialSelection;
use App\Filament\Tenant\Resources\StockTransfers\StockTransferResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStockTransfer extends CreateRecord
{
    use HandlesSerialSelection;

    protected static string $resource = StockTransferResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        // dd($data);
        return $data;
    }
}
