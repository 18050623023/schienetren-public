<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Employee;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class EmployeePolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:employee');
    }

    public function view(authUser $authUser, Employee $employee): bool
    {
        return $this->checkOwnership($authUser, 'view:employee', $employee);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:employee');
    }

    public function update(authUser $authUser, Employee $employee): bool
    {
        return $this->checkOwnership($authUser, 'update:employee', $employee);
    }

    public function delete(authUser $authUser, Employee $employee): bool
    {
        return $this->checkOwnership($authUser, 'delete:employee', $employee);
    }

    public function restore(authUser $authUser, Employee $employee): bool
    {
        return $this->checkOwnership($authUser, 'restore:employee', $employee);
    }

    public function forceDelete(authUser $authUser, Employee $employee): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:employee', $employee);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:employee');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:employee');
    }

    public function replicate(authUser $authUser, Employee $employee): bool
    {
        return $this->checkOwnership($authUser, 'replicate:employee', $employee);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:employee');
    }

}
