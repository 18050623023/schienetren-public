<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\Subscription;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class SubscriptionPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:subscription');
    }

    public function view(authUser $authUser, Subscription $subscription): bool
    {
        return $this->checkOwnership($authUser, 'view:subscription', $subscription);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:subscription');
    }

    public function update(authUser $authUser, Subscription $subscription): bool
    {
        return $this->checkOwnership($authUser, 'update:subscription', $subscription);
    }

    public function delete(authUser $authUser, Subscription $subscription): bool
    {
        return $this->checkOwnership($authUser, 'delete:subscription', $subscription);
    }

    public function restore(authUser $authUser, Subscription $subscription): bool
    {
        return $this->checkOwnership($authUser, 'restore:subscription', $subscription);
    }

    public function forceDelete(authUser $authUser, Subscription $subscription): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:subscription', $subscription);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:subscription');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:subscription');
    }

    public function replicate(authUser $authUser, Subscription $subscription): bool
    {
        return $this->checkOwnership($authUser, 'replicate:subscription', $subscription);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:subscription');
    }

}
