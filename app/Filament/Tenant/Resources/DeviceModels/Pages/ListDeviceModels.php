<?php

namespace App\Filament\Tenant\Resources\DeviceModels\Pages;

use App\Filament\Tenant\Resources\DeviceModels\DeviceModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeviceModels extends ListRecords
{
    protected static string $resource = DeviceModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
