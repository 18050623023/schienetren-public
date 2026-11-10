<?php

namespace App\Filament\Tenant\Resources\WorkSchedules\Pages;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use App\Filament\Tenant\Resources\WorkSchedules\WorkScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkSchedule extends EditRecord
{
    protected static string $resource = WorkScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            // RestoreAction::make(),
            // ForceDeleteAction::make(),
        ];
    }
}
