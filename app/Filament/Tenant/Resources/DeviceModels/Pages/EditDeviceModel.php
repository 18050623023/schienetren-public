<?php

namespace App\Filament\Tenant\Resources\DeviceModels\Pages;

use App\Filament\Tenant\Resources\DeviceModels\DeviceModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeviceModel extends EditRecord
{
    protected static string $resource = DeviceModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
