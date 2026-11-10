<?php

namespace App\Filament\Tenant\Resources\LeaveRequests\Pages;

use App\Filament\Tenant\Resources\LeaveRequests\Action\LeaveRequestActionStatus;
use App\Filament\Tenant\Resources\LeaveRequests\LeaveRequestResource;
use App\Models\Tenant\Employee;
use App\Models\Tenant\EmployeeLeaveBalance;
use App\Models\Tenant\LeaveType;
use App\Services\LeavePolicyService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateLeaveRequest extends CreateRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // $new = app(LeavePolicyService::class)
        //     ->calculateQuota($data['employee_id'], $data['leave_type_id'], $data['start_date'], $data['end_date']);
        // dd($periodStart);

        return $data;
    }

    protected function afterCreate(): void
    {

        $files = $this->data['attachments'] ?? [];
        $names = $this->data['original_names'] ?? [];

        $this->record->attachments()->createMany(
            collect($files)->map(fn ($path) => [
                'file_name' => $names[$path] ?? null,
                'file_path' => $path,
                'created_by' => Auth::id(),
            ])->toArray()
        );

        // generate or update employee leave balance
        $leaveType = $this->record->leaveType;

        if ($this->record->status === 'approved') {
            return;
        }

        // create leave balance jika requires_approval false
        if (!$leaveType->require_approval) {
            LeaveRequestActionStatus::approveAction($this->record);
        }



    }
}
