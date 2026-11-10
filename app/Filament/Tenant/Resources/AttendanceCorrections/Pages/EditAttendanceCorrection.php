<?php

namespace App\Filament\Tenant\Resources\AttendanceCorrections\Pages;

use App\Filament\Tenant\Resources\AttendanceCorrections\AttendanceCorrectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceCorrection extends EditRecord
{
    protected static string $resource = AttendanceCorrectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
