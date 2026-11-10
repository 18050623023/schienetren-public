<?php

namespace App\Filament\Tenant\Resources\Compensation\Pages;

use App\Filament\Tenant\Resources\Compensation\CompensationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditCompensation extends EditRecord
{
    protected static string $resource = CompensationResource::class;
    // protected string $view = 'filament.resources.compensation-packages.edit';
    protected string $view = 'filament.tenant.resources.compensation-packages.edit';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
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
