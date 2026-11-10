<?php

namespace App\Filament\Tenant\Resources\DeviceTypes\Pages;

use App\Filament\Tenant\Resources\DeviceTypes\DeviceTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeviceTypes extends ListRecords
{
    protected static string $resource = DeviceTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
