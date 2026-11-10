<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\Ownable;

class Attendance extends Model implements Ownable
{

    protected $fillable = [
        'employee_assignment_id',
        'date',
        'overtime',
        'requires_schedule_review',
        'review_status',
        'reviewed_by',
        'attendance_type',
        'status',
        'notes',
    ];

    public function employeeAssignment()
    {
        return $this->belongsTo(EmployeeAssignment::class);
    }

    public function employee()
    {
        return $this->employeeAssignment()->belongsTo(Employee::class);
    }

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    public function reviewBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Query Scopes for Reporting & Filtering
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        if ($from && $to) {
            return $query->whereBetween('date', [$from, $to]);
        }
        if ($from) {
            return $query->where('date', '>=', $from);
        }
        if ($to) {
            return $query->where('date', '<=', $to);
        }
        return $query;
    }

    public function scopeForBranch($query, $branchId)
    {
        if (!$branchId) {
            return $query;
        }
        return $query->whereHas('employeeAssignment', fn($q) => $q->where('branch_id', $branchId));
    }

    public function scopeForEmployee($query, $employeeId)
    {
        if (!$employeeId) {
            return $query;
        }
        return $query->whereHas('employeeAssignment', fn($q) => $q->where('employee_id', $employeeId));
    }

    public function scopeStatus($query, $status)
    {
        if (!$status) {
            return $query;
        }
        return $query->where('status', $status);
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->employeeAssignment->employee->user_id ?? null;
    }
}
