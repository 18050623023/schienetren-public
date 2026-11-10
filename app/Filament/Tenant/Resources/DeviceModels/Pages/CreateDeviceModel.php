<?php

namespace App\Filament\Tenant\Resources\DeviceModels\Pages;

use App\Filament\Tenant\Resources\DeviceModels\DeviceModelResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeviceModel extends CreateRecord
{
    protected static string $resource = DeviceModelResource::class;
}
