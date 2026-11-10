<?php

namespace App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\Pages;

use App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\RepairWarrantyClaimResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRepairWarrantyClaim extends EditRecord
{
    protected static string $resource = RepairWarrantyClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
