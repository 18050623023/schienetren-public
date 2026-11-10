<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\Role;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(authUser $authUser): bool
    {
        return $authUser->can('viewAny:role');
    }

    public function view(authUser $authUser, Role $role): bool
    {
        return $authUser->can('view:role');
    }

    public function create(authUser $authUser): bool
    {
        return $authUser->can('create:role');
    }

    public function update(authUser $authUser, Role $role): bool
    {
        return $authUser->can('update:role');
    }

    public function delete(authUser $authUser, Role $role): bool
    {
        return $authUser->can('delete:role');
    }

    public function restore(authUser $authUser, Role $role): bool
    {
        return $authUser->can('restore:role');
    }

    public function forceDelete(authUser $authUser, Role $role): bool
    {
        return $authUser->can('forceDelete:role');
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $authUser->can('forceDeleteAny:role');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $authUser->can('restoreAny:role');
    }

    public function replicate(authUser $authUser, Role $role): bool
    {
        return $authUser->can('replicate:role');
    }

    public function reorder(authUser $authUser): bool
    {
        return $authUser->can('reorder:role');
    }

}
