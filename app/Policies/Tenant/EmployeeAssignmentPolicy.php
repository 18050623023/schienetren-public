<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\EmployeeAssignment;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class EmployeeAssignmentPolicy
{
    use HandlesAuthorization, HasRecordRules;

    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:employee_assignment');
    }

    public function view(authUser $authUser, EmployeeAssignment $employee_assignment): bool
    {
        return $this->checkOwnership($authUser, 'view:employee_assignment', $employee_assignment);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:employee_assignment');
    }

    public function update(authUser $authUser, EmployeeAssignment $employee_assignment): bool
    {
        return $this->checkOwnership($authUser, 'update:employee_assignment', $employee_assignment);
    }

    public function delete(authUser $authUser, EmployeeAssignment $employee_assignment): bool
    {
        return $this->checkOwnership($authUser, 'delete:employee_assignment', $employee_assignment);
    }

    public function restore(authUser $authUser, EmployeeAssignment $employee_assignment): bool
    {
        return $this->checkOwnership($authUser, 'restore:employee_assignment', $employee_assignment);
    }

    public function forceDelete(authUser $authUser, EmployeeAssignment $employee_assignment): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:employee_assignment', $employee_assignment);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:employee_assignment');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:employee_assignment');
    }

    public function replicate(authUser $authUser, EmployeeAssignment $employee_assignment): bool
    {
        return $this->checkOwnership($authUser, 'replicate:employee_assignment', $employee_assignment);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:employee_assignment');
    }

}
