<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\Ownable;

class EmployeeLeaveBalance extends Model implements Ownable
{
    
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'period_start',
        'period_end',
        'quota',
        'used',
        'remaining',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Query Scopes for Reporting & Filtering
     */
    public function scopeForBranch($query, $branchId)
    {
        if (!$branchId) {
            return $query;
        }
        return $query->whereHas('employee.employeeAssignments', fn($q) => $q->where('branch_id', $branchId));
    }

    public function scopeForEmployee($query, $employeeId)
    {
        if (!$employeeId) {
            return $query;
        }
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForYear($query, $year)
    {
        if (!$year) {
            return $query;
        }
        return $query->where(function ($q) use ($year) {
            $q->whereYear('period_start', $year)
              ->orWhereYear('period_end', $year);
        });
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->employee->user_id ?? null;
    }
}
