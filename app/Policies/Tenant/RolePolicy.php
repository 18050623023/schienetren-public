<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Role;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class RolePolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:role');
    }

    public function view(authUser $authUser, Role $role): bool
    {
        return $this->checkOwnership($authUser, 'view:role', $role);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:role');
    }

    public function update(authUser $authUser, Role $role): bool
    {
        return $this->checkOwnership($authUser, 'update:role', $role);
    }

    public function delete(authUser $authUser, Role $role): bool
    {
        return $this->checkOwnership($authUser, 'delete:role', $role);
    }

    public function restore(authUser $authUser, Role $role): bool
    {
        return $this->checkOwnership($authUser, 'restore:role', $role);
    }

    public function forceDelete(authUser $authUser, Role $role): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:role', $role);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:role');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:role');
    }

    public function replicate(authUser $authUser, Role $role): bool
    {
        return $this->checkOwnership($authUser, 'replicate:role', $role);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:role');
    }

}
