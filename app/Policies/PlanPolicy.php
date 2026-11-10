<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\Plan;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class PlanPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:plan');
    }

    public function view(authUser $authUser, Plan $plan): bool
    {
        return $this->checkOwnership($authUser, 'view:plan', $plan);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:plan');
    }

    public function update(authUser $authUser, Plan $plan): bool
    {
        return $this->checkOwnership($authUser, 'update:plan', $plan);
    }

    public function delete(authUser $authUser, Plan $plan): bool
    {
        return $this->checkOwnership($authUser, 'delete:plan', $plan);
    }

    public function restore(authUser $authUser, Plan $plan): bool
    {
        return $this->checkOwnership($authUser, 'restore:plan', $plan);
    }

    public function forceDelete(authUser $authUser, Plan $plan): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:plan', $plan);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:plan');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:plan');
    }

    public function replicate(authUser $authUser, Plan $plan): bool
    {
        return $this->checkOwnership($authUser, 'replicate:plan', $plan);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:plan');
    }

}
