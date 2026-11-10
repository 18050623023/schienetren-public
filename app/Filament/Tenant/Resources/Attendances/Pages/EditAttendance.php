<?php

namespace App\Filament\Tenant\Resources\Attendances\Pages;

use App\Filament\Tenant\Resources\Attendances\AttendanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAttendance extends EditRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
