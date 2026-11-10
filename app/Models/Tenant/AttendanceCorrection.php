<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\Ownable;

class AttendanceCorrection extends Model implements Ownable
{

    protected $fillable = [
        'attendance_id',
        'employee_assignment_id',
        'date',
        'actual_time_in',
        'actual_time_out',
        'status',
        'reason',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function employeeAssignment()
    {
        return $this->belongsTo(EmployeeAssignment::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->employeeAssignment->employee->user_id ?? null;
    }
}
