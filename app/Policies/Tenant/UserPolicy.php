<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class UserPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:user');
    }

    public function view(authUser $authUser, User $user): bool
    {
        return $this->checkOwnership($authUser, 'view:user', $user);
    }

    public function create(authUser $authUser): bool
    {
        if (!User::checkUserLimit()->passed) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:user');
    }

    public function update(authUser $authUser, User $user): bool
    {
        return $this->checkOwnership($authUser, 'update:user', $user);
    }

    public function delete(authUser $authUser, User $user): bool
    {
        return $this->checkOwnership($authUser, 'delete:user', $user);
    }

    public function restore(authUser $authUser, User $user): bool
    {
        return $this->checkOwnership($authUser, 'restore:user', $user);
    }

    public function forceDelete(authUser $authUser, User $user): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:user', $user);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:user');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:user');
    }

    public function replicate(authUser $authUser, User $user): bool
    {
        return $this->checkOwnership($authUser, 'replicate:user', $user);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:user');
    }

}
