<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Contracts\Ownable;

class Employee extends Model implements Ownable
{
    use SoftDeletes;

    protected $fillable = [
        'code_pegawai',
        'user_id',
        'phone',
        'whatsapp',
        'address',
        'gender',
        'join_date',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getNameAttribute()
    {
        return $this->user?->name;
    }

    public function assignments()
    {
        return $this->hasMany(EmployeeAssignment::class);
    }

    public function skills()
    {
        return $this->hasMany(SkillEmployee::class);

    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leavesBalance()
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    // public function employeeRoles()
    // {
    //     return $this->belongsToMany(EmployeeRole::class, 'employee_role_assignments', 'employee_id', 'employee_role_id');
    // }

    public function employeeAssignments()
    {
        return $this->hasMany(EmployeeAssignment::class);
    }

    // balance
    public function leaveBalances()
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->user_id;
    }
}
