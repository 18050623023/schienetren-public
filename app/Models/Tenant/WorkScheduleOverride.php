<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\Ownable;

class WorkScheduleOverride extends Model implements Ownable
{
    protected $fillable = [
        'employee_assignment_id',
        'work_shift_id',
        'date',
        'is_working_days',
        'reason',
        'type',
        'approval_status',
        'approved_by',
        'rejected_by',
        'approved_at',
        'rejected_at',
        'created_by',
    ];

    protected $casts = [
        'is_working_days' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (WorkScheduleOverride $override) {
            if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::SHIFT_MANAGEMENT)) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []),
                    ['feature' => 'Fitur Shift Management tidak aktif pada paket tenant ini.']
                );
            }
        });
    }

    public function employeeAssignment()
    {
        return $this->belongsTo(EmployeeAssignment::class);
    }

    public function workShift()
    {
        return $this->belongsTo(WorkShift::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Scope a query to only include approved overrides for a specific assignment and date.
     */
    public function scopeApprovedForAssignmentAndDate($query, $assignmentId, $date)
    {
        return $query->where('employee_assignment_id', $assignmentId)
                     ->whereDate('date', $date)
                     ->where('approval_status', 'approved');
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->employeeAssignment->employee->user_id ?? null;
    }
}
