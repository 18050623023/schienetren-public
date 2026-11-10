<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Contracts\Ownable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\PermissionRegistrar;

class EmployeeAssignment extends Model implements Ownable
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'work_schedule_id',
        'start_date',
        'end_date',
    ];

    protected static function booted(): void
    {
        static::creating(function (EmployeeAssignment $assignment) {
            $assignment->work_schedule_id ??= WorkSchedule::getDefaultSchedule()?->id;
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function compensationAssignments()
    {
        return $this->hasMany(CompensationAssignment::class);
    }

    public function employeeRoleAssignments()
    {
        return $this->hasMany(EmployeeRoleAssignment::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function repairTechnicians()
    {
        return $this->hasMany(RepairTechnician::class);
    }

    public function repairTechnicianCommissions(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(RepairTechnicianCommission::class, RepairTechnician::class);
    }

    public function compensationPackages(): BelongsToMany
    {
        return $this->belongsToMany(
            CompensationPackage::class,
            'compensation_assignments',
        )
            ->withPivot([
                'start_date',
                'end_date',
            ])
            ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(config('permission.models.role'), 'employee_role_assignments', 'employee_assignment_id', app(PermissionRegistrar::class)->pivotRole);
    }

    /**
     * Check if the given date is a working day based on the work schedule.
     */
    public function isWorkingDay(\Carbon\Carbon $date): bool
    {
        if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::SHIFT_MANAGEMENT)) {
            return (int) $date->dayOfWeekIso <= 6;
        }

        $day = $this->workSchedule?->days?->first(function ($day) use ($date) {
            return (int) $day->day_of_week === (int) $date->dayOfWeekIso
                || (int) $day->day_of_week === (int) $date->dayOfWeek;
        });

        return (bool) ($day?->is_working_days ?? false);
    }

    /**
     * Check if an active assignment already exists for the given employee and branch.
     */
    public static function hasActiveAssignment($employeeId, $branchId): bool
    {
        return self::query()
            ->where('employee_id', $employeeId)
            ->where('branch_id', $branchId)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', today());
            })
            ->exists();
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->employee->user_id ?? null;
    }
}
