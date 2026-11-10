<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Branch;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class BranchPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::MAX_BRANCH)) {
            return false;
        }
        return $this->checkOwnership($authUser, 'viewAny:branch');
    }

    public function view(authUser $authUser, Branch $branch): bool
    {
        return $this->checkOwnership($authUser, 'view:branch', $branch);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:branch');
    }

    public function update(authUser $authUser, Branch $branch): bool
    {
        return $this->checkOwnership($authUser, 'update:branch', $branch);
    }

    public function delete(authUser $authUser, Branch $branch): bool
    {
        return $this->checkOwnership($authUser, 'delete:branch', $branch);
    }

    public function restore(authUser $authUser, Branch $branch): bool
    {
        return $this->checkOwnership($authUser, 'restore:branch', $branch);
    }

    public function forceDelete(authUser $authUser, Branch $branch): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:branch', $branch);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:branch');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:branch');
    }

    public function replicate(authUser $authUser, Branch $branch): bool
    {
        return $this->checkOwnership($authUser, 'replicate:branch', $branch);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:branch');
    }

}
