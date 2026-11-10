<?php

namespace App\Filament\Tenant\Resources\Repairs\Pages;

use App\Enums\RepairStatus;
use App\Filament\Tenant\Resources\Repairs\Pages\Concerns\HandlesRepairItemSelection;
use App\Filament\Tenant\Resources\Repairs\RepairResource;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Repair;
use App\Services\RepairService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateRepair extends CreateRecord
{
    use HandlesRepairItemSelection;

    protected static string $resource = RepairResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::mutateDataBeforeCreate($data);
    }

    // protected function afterCreate(): void
    // {
    //     \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::afterSave($this->data, $this->record);
    // }
}
