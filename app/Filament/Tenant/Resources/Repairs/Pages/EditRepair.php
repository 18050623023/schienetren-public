<?php

namespace App\Filament\Tenant\Resources\Repairs\Pages;

use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use App\Filament\Tenant\Resources\Repairs\Pages\Concerns\HandlesRepairItemSelection;
use App\Filament\Tenant\Resources\Repairs\RepairResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRepair extends EditRecord
{
    use HandlesRepairItemSelection;

    protected static string $resource = RepairResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
            // RestoreAction::make(),
            // ForceDeleteAction::make(),
        ];
    }

    // protected function mutateFormDataBeforeFill(array $data): array
    // {
    //     return \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::mutateDataBeforeFill($data, $this->record);
    // }

    // protected function afterSave(): void
    // {
    //     \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::afterSave($this->data, $this->record);
    // }
}
