<?php

namespace App\Filament\Tenant\Resources\WorkShifts\Pages;

use App\Filament\Tenant\Resources\WorkShifts\WorkShiftResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkShifts extends ListRecords
{
    protected static string $resource = WorkShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
