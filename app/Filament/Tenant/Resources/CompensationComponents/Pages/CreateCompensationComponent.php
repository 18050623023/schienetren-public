<?php

namespace App\Filament\Tenant\Resources\CompensationComponents\Pages;

use App\Filament\Tenant\Resources\CompensationComponents\CompensationComponentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompensationComponent extends CreateRecord
{
    protected static string $resource = CompensationComponentResource::class;
}
