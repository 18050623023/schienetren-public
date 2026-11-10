<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\EmployeeLeaveBalance;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class EmployeeLeaveBalancePolicy
{
    use HandlesAuthorization, HasRecordRules;

    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:employee_leave_balance');
    }

    public function view(authUser $authUser, EmployeeLeaveBalance $employee_leave_balance): bool
    {
        if ($authUser->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) {
            return true;
        }
        return $authUser->can('view:employee_leave_balance')
            && $employee_leave_balance->employee->user_id === $authUser->id;
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:employee_leave_balance');
    }

    public function update(authUser $authUser, EmployeeLeaveBalance $employee_leave_balance): bool
    {
        if ($authUser->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) {
            return true;
        }

        return $authUser->can('update:employee_leave_balance')
            && $employee_leave_balance->employee->user_id === $authUser->id;
    }

    public function delete(authUser $authUser, EmployeeLeaveBalance $employee_leave_balance): bool
    {
        return $this->checkOwnership($authUser, 'delete:employee_leave_balance', $employee_leave_balance);
    }

    public function restore(authUser $authUser, EmployeeLeaveBalance $employee_leave_balance): bool
    {
        return $this->checkOwnership($authUser, 'restore:employee_leave_balance', $employee_leave_balance);
    }

    public function forceDelete(authUser $authUser, EmployeeLeaveBalance $employee_leave_balance): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:employee_leave_balance', $employee_leave_balance);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:employee_leave_balance');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:employee_leave_balance');
    }

    public function replicate(authUser $authUser, EmployeeLeaveBalance $employee_leave_balance): bool
    {
        return $this->checkOwnership($authUser, 'replicate:employee_leave_balance', $employee_leave_balance);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:employee_leave_balance');
    }

}
