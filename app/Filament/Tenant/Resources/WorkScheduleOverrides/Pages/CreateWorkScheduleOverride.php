<?php

namespace App\Filament\Tenant\Resources\WorkScheduleOverrides\Pages;

use App\Filament\Tenant\Resources\WorkScheduleOverrides\WorkScheduleOverrideResource;
use App\Models\Tenant\EmployeeAssignment;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreateWorkScheduleOverride extends CreateRecord
{
    protected static string $resource = WorkScheduleOverrideResource::class;
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // dd();

        $exists = $this->getModel()::where('date', $data['date'])->where('employee_assignment_id', $data['employee_assignment_id'])->exists();

        if ($exists) {
            Notification::make()
                ->danger()
                ->title('Override sudah ada')
                ->send();

            throw new Halt();
        }

        $date = Carbon::parse($data['date']);
        $assignment = EmployeeAssignment::find($data['employee_assignment_id']);

        $assignmentWorkingDay = $assignment->isWorkingDay($date);

        if (!$assignmentWorkingDay && !$data['is_working_days']) {
            Notification::make()
                ->danger()
                ->title('Tanggal tersebut juga bukan hari kerja')
                ->send();

            throw new Halt();
        }

        $data['type'] = match (true) {
            $assignmentWorkingDay && !$data['is_working_days'] => 'force_off_day',
            !$assignmentWorkingDay && $data['is_working_days'] => 'force_working_day',
            $assignmentWorkingDay && $data['is_working_days'] => 'custom_shift',
            default => null, // sesuaikan jika kombinasi false/false memang valid
        };

        return $data;
    }
}
