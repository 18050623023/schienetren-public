<?php

namespace App\Filament\Tenant\Resources\WorkSchedules\Pages;

use App\Filament\Tenant\Resources\WorkSchedules\WorkScheduleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkSchedule extends CreateRecord
{
    protected static string $resource = WorkScheduleResource::class;
}
