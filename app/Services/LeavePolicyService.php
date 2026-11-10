<?php

namespace App\Services;

use App\Models\Tenant\Employee;
use App\Models\Tenant\LeaveRequest;
use App\Models\Tenant\LeaveType;
use Carbon\Carbon;

class LeavePolicyService
{
    public function calculateRemaining(Employee $employee, LeaveType $leaveType, string $date): ?int {


        if ($leaveType->is_unlimited) {
            return null;
        }

        [$start, $end] = $this->resolvePeriod(
            $employee,
            $leaveType,
            $date
        );

        $quota = $this->calculateFirstQuota(
            $employee,
            $leaveType,
            $start,
            $end
        );

        $used = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'approved')
            ->whereBetween('start_date', [$start, $end])
            ->sum('total_days');

        return max($quota - $used, 0);
    }

    public function resolvePeriod(Employee $employee, LeaveType $leaveType, string $date): array
    {
        $date = Carbon::parse($date);
        $joinDate = Carbon::parse($employee->join_date);

        [$start, $end] = match ($leaveType->reset_period) {

            'monthly' => [
                $date->copy()->startOfMonth(),
                $date->copy()->endOfMonth(),
            ],

            'yearly' => [
                $date->copy()->startOfYear(),
                $date->copy()->endOfYear(),
            ],

            default => [
                $date->copy()->startOfYear(),
                $date->copy()->endOfYear(),
            ],
        };

        /*
        |--------------------------------------------------------------------------
        | Adjust employee baru join
        |--------------------------------------------------------------------------
        */

        if ($joinDate->between($start, $end)) {
            $start = $joinDate->copy();
        }

        return [$start, $end];
    }

    public function calculateFirstQuota(Employee $employee, LeaveType $leaveType, string $periodStart, string $periodEnd): ?int
    {
        // dd($leaveType);
        $periodStart = Carbon::parse($periodStart);
        $periodEnd = Carbon::parse($periodEnd);
        $joinDate = Carbon::parse($employee->join_date);

        // employee join setelah periode selesai
        if ($joinDate->gt($periodEnd)) {
            return 0;
        }

        // dd($leaveType);
        return match ($leaveType->reset_period) {

            'yearly' => $this->calculateYearlyProrate(
                $joinDate,
                $leaveType->default_quota,
                $periodStart
            ),

            'monthly' => $this->calculateMonthlyProrate(
                $joinDate,
                $leaveType->default_quota ?? 0,
                $periodStart
            ),

            default => $leaveType->default_quota,
        };
    }

    protected function calculateYearlyProrate(Carbon $joinDate, int $quota, Carbon $periodStart): int
    {

        if ($joinDate->year !== $periodStart->year) {
            return $quota;
        }

        $remainingMonths = 12 - $joinDate->month + 1;

        return floor(($quota / 12) * $remainingMonths);
    }

    protected function calculateMonthlyProrate(Carbon $joinDate, int $quota, Carbon $periodStart): int
    {

        // join di bulan berbeda → full quota
        if (
            $joinDate->month !== $periodStart->month ||
            $joinDate->year !== $periodStart->year
        ) {
            return $quota;
        }

        // join tengah bulan
        $daysInMonth = $joinDate->daysInMonth;
        $remainingDays = $daysInMonth - $joinDate->day + 1;

        return floor(($quota / $daysInMonth) * $remainingDays);
    }
}
