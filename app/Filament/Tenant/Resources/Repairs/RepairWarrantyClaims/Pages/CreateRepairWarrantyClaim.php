<?php

namespace App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\Pages;

use App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\RepairWarrantyClaimResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRepairWarrantyClaim extends CreateRecord
{
    protected static string $resource = RepairWarrantyClaimResource::class;
}
