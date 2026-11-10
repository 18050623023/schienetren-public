<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\EmployeeRole;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class EmployeeRolePolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:employee_role');
    }

    public function view(authUser $authUser, EmployeeRole $employee_role): bool
    {
        return $this->checkOwnership($authUser, 'view:employee_role', $employee_role);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:employee_role');
    }

    public function update(authUser $authUser, EmployeeRole $employee_role): bool
    {
        return $this->checkOwnership($authUser, 'update:employee_role', $employee_role);
    }

    public function delete(authUser $authUser, EmployeeRole $employee_role): bool
    {
        return $this->checkOwnership($authUser, 'delete:employee_role', $employee_role);
    }

    public function restore(authUser $authUser, EmployeeRole $employee_role): bool
    {
        return $this->checkOwnership($authUser, 'restore:employee_role', $employee_role);
    }

    public function forceDelete(authUser $authUser, EmployeeRole $employee_role): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:employee_role', $employee_role);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:employee_role');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:employee_role');
    }

    public function replicate(authUser $authUser, EmployeeRole $employee_role): bool
    {
        return $this->checkOwnership($authUser, 'replicate:employee_role', $employee_role);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:employee_role');
    }

}
