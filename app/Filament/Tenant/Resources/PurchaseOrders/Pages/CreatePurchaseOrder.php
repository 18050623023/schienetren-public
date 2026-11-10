<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\Pages;

use App\Filament\Tenant\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     // tangkap semua data form
    //     dd($data);

    //     return $data;
    // }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
