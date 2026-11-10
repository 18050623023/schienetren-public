<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    

    protected $fillable = [
        'name',
        'code',
        'default_quota',
        'quota_per_request',
        'reset_period',
        'is_paid',
        'require_approval',
        'is_unlimited',
    ];

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function employeeLeaveBalances()
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }
}
