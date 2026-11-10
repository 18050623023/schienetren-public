<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\Feature;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class FeaturePolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:feature');
    }

    public function view(authUser $authUser, Feature $feature): bool
    {
        return $this->checkOwnership($authUser, 'view:feature', $feature);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:feature');
    }

    public function update(authUser $authUser, Feature $feature): bool
    {
        return $this->checkOwnership($authUser, 'update:feature', $feature);
    }

    public function delete(authUser $authUser, Feature $feature): bool
    {
        return $this->checkOwnership($authUser, 'delete:feature', $feature);
    }

    public function restore(authUser $authUser, Feature $feature): bool
    {
        return $this->checkOwnership($authUser, 'restore:feature', $feature);
    }

    public function forceDelete(authUser $authUser, Feature $feature): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:feature', $feature);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:feature');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:feature');
    }

    public function replicate(authUser $authUser, Feature $feature): bool
    {
        return $this->checkOwnership($authUser, 'replicate:feature', $feature);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:feature');
    }

}
