<?php

namespace App\Filament\Tenant\Widgets;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\EmployeeLeaveBalances\EmployeeLeaveBalanceResource;
use App\Filament\Tenant\Resources\LeaveRequests\LeaveRequestResource;
use App\Models\Tenant\EmployeeLeaveBalance;
use App\Models\Tenant\Employee;
use App\Models\Tenant\LeaveType;
use App\Services\LeavePolicyService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class LeaveBalanceStatWidget extends StatsOverviewWidget
{
    public int|string|null $employeeId = null;

    public static function canView(): bool
    {
        return tenant()->hasFeatureCode(TenantFeature::LEAVE);
    }

    public function mount( $employeeId = null): void
    {
        $this->employeeId = $employeeId
            ? (int) $employeeId
            : null;
        // dd($this->employeeId);
    }

    protected function getStats(): array
    {

        if (blank($this->employeeId) || ! $this->employeeId) {
            return [
                Stat::make(
                    'Employee Leave Balance',
                    'Please select an employee'
                ),
            ];
        }

        $LeavePolicyService = app(LeavePolicyService::class);
        // dd($LeavePolicyService->calculateRemaining(Employee::find($this->employeeId), LeaveType::find(2), now()->format('Y-m-d')));

        $usedLeaves  = LeaveRequestResource::getEloquentQuery()
                                ->where('employee_id', $this->employeeId)
                                ->where('status', 'approved')
                                ->selectRaw('leave_type_id, SUM(total_days) as total')
                                ->groupBy('leave_type_id')
                                ->pluck('total', 'leave_type_id');

        $employeeLeaveBalance = EmployeeLeaveBalance::query()
                                    ->where('employee_id', $this->employeeId)
                                    ->get()
                                    ->keyBy('leave_type_id');

        $employee = Employee::find($this->employeeId);

        $leavePolicyService = app(LeavePolicyService::class);

        return  LeaveType::all()
            ->map(function (LeaveType $leaveType) use ($employeeLeaveBalance, $usedLeaves, $leavePolicyService, $employee) {


                [$periodStart, $periodEnd] = $leavePolicyService->resolvePeriod(
                    $employee,
                    $leaveType,
                    now()->toDateString()
                );

                $quota = $leavePolicyService->calculateFirstQuota(
                    $employee,
                    $leaveType,
                    $periodStart,
                    $periodEnd
                );


                $balance = $employeeLeaveBalance[$leaveType->id] ?? null;

                $used = $usedLeaves[$leaveType->id] ?? 0;

                if ($leaveType->is_unlimited) {
                    return Stat::make(
                        'Leave ' . $leaveType->name,
                        intval($used)
                    )->description(' days used');
                }

                return Stat::make(
                    'Leave ' . $leaveType->name,
                    ($balance?->remaining ?? 0) . ' / ' . ($quota ?? 0)
                )->description("{$used} days used");
            })
            ->toArray();

    }
}
