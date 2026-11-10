<?php

namespace App\Filament\Tenant\Resources\WorkScheduleOverrides\Pages;

use App\Filament\Tenant\Resources\WorkScheduleOverrides\WorkScheduleOverrideResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkScheduleOverride extends EditRecord
{
    protected static string $resource = WorkScheduleOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
