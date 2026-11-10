<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Permission;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class PermissionPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:permission');
    }

    public function view(authUser $authUser, Permission $permission): bool
    {
        return $this->checkOwnership($authUser, 'view:permission', $permission);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:permission');
    }

    public function update(authUser $authUser, Permission $permission): bool
    {
        return $this->checkOwnership($authUser, 'update:permission', $permission);
    }

    public function delete(authUser $authUser, Permission $permission): bool
    {
        return $this->checkOwnership($authUser, 'delete:permission', $permission);
    }

    public function restore(authUser $authUser, Permission $permission): bool
    {
        return $this->checkOwnership($authUser, 'restore:permission', $permission);
    }

    public function forceDelete(authUser $authUser, Permission $permission): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:permission', $permission);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:permission');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:permission');
    }

    public function replicate(authUser $authUser, Permission $permission): bool
    {
        return $this->checkOwnership($authUser, 'replicate:permission', $permission);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:permission');
    }

}
