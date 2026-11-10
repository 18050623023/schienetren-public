<?php

namespace App\Filament\Tenant\Resources\AttendanceCorrections\Pages;

use App\Filament\Tenant\Resources\AttendanceCorrections\AttendanceCorrectionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;

class CreateAttendanceCorrection extends CreateRecord
{
    protected static string $resource = AttendanceCorrectionResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $exists = $this->getModel()::where('date', $data['date'])->where('attendance_id', $data['attendance_id'])->exists();

        if ($exists) {
            Notification::make()
                ->danger()
                ->title('Correction sudah ada')
                ->send();

            throw new Halt();
        }

        // 1. Set nilai default jika status kosong/null
        if (empty($data['status'])) {
            $data['status'] = 'pending';
        }

        // 2. Eksekusi logika review jika status bukan 'pending'
        if ($data['status'] !== 'pending') {
            $data['reviewed_by'] = Auth::id();
            $data['reviewed_at'] = now();
        }

        // dd($data);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
