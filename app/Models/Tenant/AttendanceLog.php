<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\Ownable;

class AttendanceLog extends Model implements Ownable
{
    use \App\Traits\HasDeletableFiles;

    public array $deletableFileColumns = ['photo'];
    public array $deletableFileDirectories = ['images/attendance'];

    protected $fillable = [
        'attendance_id',
        'employee_assignment_id',
        'logged_at',
        'photo',
        'latitude',
        'longitude',
        'accuracy',
        'distance',
        'late_minutes',
        'early_minutes',
        'type',
        'notes',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function employeeAssignment()
    {
        return $this->belongsTo(EmployeeAssignment::class);
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->employeeAssignment->employee->user_id ?? null;
    }
}
