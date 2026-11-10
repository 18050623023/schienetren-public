<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\Permission;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(authUser $authUser): bool
    {
        return $authUser->can('viewAny:permission');
    }

    public function view(authUser $authUser, Permission $permission): bool
    {
        return $authUser->can('view:permission');
    }

    public function create(authUser $authUser): bool
    {
        return $authUser->can('create:permission');
    }

    public function update(authUser $authUser, Permission $permission): bool
    {
        return $authUser->can('update:permission');
    }

    public function delete(authUser $authUser, Permission $permission): bool
    {
        return $authUser->can('delete:permission');
    }

    public function restore(authUser $authUser, Permission $permission): bool
    {
        return $authUser->can('restore:permission');
    }

    public function forceDelete(authUser $authUser, Permission $permission): bool
    {
        return $authUser->can('forceDelete:permission');
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $authUser->can('forceDeleteAny:permission');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $authUser->can('restoreAny:permission');
    }

    public function replicate(authUser $authUser, Permission $permission): bool
    {
        return $authUser->can('replicate:permission');
    }

    public function reorder(authUser $authUser): bool
    {
        return $authUser->can('reorder:permission');
    }

}
