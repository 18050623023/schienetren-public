<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(authUser $authUser): bool
    {
        return $authUser->can('viewAny:user');
    }

    public function view(authUser $authUser, User $user): bool
    {
        return $authUser->can('view:user');
    }

    public function create(authUser $authUser): bool
    {
        return $authUser->can('create:user');
    }

    public function update(authUser $authUser, User $user): bool
    {
        return $authUser->can('update:user');
    }

    public function delete(authUser $authUser, User $user): bool
    {
        return $authUser->can('delete:user');
    }

    public function restore(authUser $authUser, User $user): bool
    {
        return $authUser->can('restore:user');
    }

    public function forceDelete(authUser $authUser, User $user): bool
    {
        return $authUser->can('forceDelete:user');
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $authUser->can('forceDeleteAny:user');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $authUser->can('restoreAny:user');
    }

    public function replicate(authUser $authUser, User $user): bool
    {
        return $authUser->can('replicate:user');
    }

    public function reorder(authUser $authUser): bool
    {
        return $authUser->can('reorder:user');
    }

}
