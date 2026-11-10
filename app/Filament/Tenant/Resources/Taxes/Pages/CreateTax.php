<?php

namespace App\Filament\Tenant\Resources\Taxes\Pages;

use App\Filament\Tenant\Resources\Taxes\TaxResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTax extends CreateRecord
{
    protected static string $resource = TaxResource::class;
}
