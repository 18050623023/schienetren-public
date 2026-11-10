<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\Addon;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class AddonPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:addon');
    }

    public function view(authUser $authUser, Addon $addon): bool
    {
        return $this->checkOwnership($authUser, 'view:addon', $addon);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:addon');
    }

    public function update(authUser $authUser, Addon $addon): bool
    {
        return $this->checkOwnership($authUser, 'update:addon', $addon);
    }

    public function delete(authUser $authUser, Addon $addon): bool
    {
        return $this->checkOwnership($authUser, 'delete:addon', $addon);
    }

    public function restore(authUser $authUser, Addon $addon): bool
    {
        return $this->checkOwnership($authUser, 'restore:addon', $addon);
    }

    public function forceDelete(authUser $authUser, Addon $addon): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:addon', $addon);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:addon');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:addon');
    }

    public function replicate(authUser $authUser, Addon $addon): bool
    {
        return $this->checkOwnership($authUser, 'replicate:addon', $addon);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:addon');
    }

}
