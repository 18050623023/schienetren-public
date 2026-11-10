<?php

namespace App\Filament\Tenant\Resources\LeaveRequests\Action;

use App\Models\Tenant\Employee;
use App\Models\Tenant\LeaveRequest;
use App\Models\Tenant\EmployeeLeaveBalance;
use App\Models\Tenant\LeaveType;
use App\Services\LeavePolicyService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveRequestActionStatus
{
    // protected static string $name = 'status';

    public static function approveAction(LeaveRequest $leave): void
    {
        DB::transaction(function () use ($leave) {

            // lock request
            $leave = LeaveRequest::lockForUpdate()->findOrFail($leave->id);

            // prevent double approve
            if ($leave->status === 'approved') {
                return;
            }

            $employee = $leave->employee;
            $leaveType = $leave->leaveType;

            $leave->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            if($leaveType->is_unlimited) {
                return;
            };


            // $service = new LeavePolicyService;
            $service = app(LeavePolicyService::class);

            [$periodStart, $periodEnd] = $service->resolvePeriod(
                $employee,
                $leaveType,
                $leave->start_date
            );

            $quota = $service->calculateFirstQuota(
                $employee,
                $leaveType,
                $periodStart,
                $periodEnd
            );


            $balance = EmployeeLeaveBalance::lockForUpdate()
                ->firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                    ],
                    [
                        'quota' => $quota,
                        'used' => 0,
                        'remaining' => $quota,
                    ]
                );

            $balance->increment('used', $leave->total_days);

            $balance->update([
                'quota' => $quota,
                'remaining' => $quota - $balance->fresh()->used,
            ]);
        });

        Notification::make()
            ->success()
            ->title('Leave Request Approved')
            ->send();
    }
}
