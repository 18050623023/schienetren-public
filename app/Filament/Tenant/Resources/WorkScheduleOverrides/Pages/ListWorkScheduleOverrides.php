<?php

namespace App\Filament\Tenant\Resources\WorkScheduleOverrides\Pages;

use App\Filament\Tenant\Resources\WorkScheduleOverrides\WorkScheduleOverrideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkScheduleOverrides extends ListRecords
{
    protected static string $resource = WorkScheduleOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false),
        ];
    }
}
