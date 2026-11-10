<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Discount;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class DiscountPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:discount');
    }

    public function view(authUser $authUser, Discount $discount): bool
    {
        return $this->checkOwnership($authUser, 'view:discount', $discount);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:discount');
    }

    public function update(authUser $authUser, Discount $discount): bool
    {
        return $this->checkOwnership($authUser, 'update:discount', $discount);
    }

    public function delete(authUser $authUser, Discount $discount): bool
    {
        return $this->checkOwnership($authUser, 'delete:discount', $discount);
    }

    public function restore(authUser $authUser, Discount $discount): bool
    {
        return $this->checkOwnership($authUser, 'restore:discount', $discount);
    }

    public function forceDelete(authUser $authUser, Discount $discount): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:discount', $discount);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:discount');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:discount');
    }

    public function replicate(authUser $authUser, Discount $discount): bool
    {
        return $this->checkOwnership($authUser, 'replicate:discount', $discount);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:discount');
    }

}
