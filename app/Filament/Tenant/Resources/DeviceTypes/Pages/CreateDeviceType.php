<?php

namespace App\Filament\Tenant\Resources\DeviceTypes\Pages;

use App\Filament\Tenant\Resources\DeviceTypes\DeviceTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDeviceType extends CreateRecord
{
    protected static string $resource = DeviceTypeResource::class;
}
