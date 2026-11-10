<?php

namespace App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\Pages;

use App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\RepairWarrantyClaimResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRepairWarrantyClaims extends ListRecords
{
    protected static string $resource = RepairWarrantyClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make()
            //  ->visible(),
        ];
    }
}
