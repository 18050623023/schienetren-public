<?php

namespace App\Filament\Tenant\Resources\DeviceTypes\Pages;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use App\Filament\Tenant\Resources\DeviceTypes\DeviceTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditDeviceType extends EditRecord
{
    protected static string $resource = DeviceTypeResource::class;
        // protected string $view = 'filament.resources.device-types.edit';
    protected string $view = 'filament.tenant.resources.device-types.edit';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                // $this->getFormContentComponent(), // This method returns a component to display the form that is defined in this resource
                $this->getRelationManagersContentComponent(), // This method returns a component to display the relation managers that are defined in this resource
            ]);
    }
}
