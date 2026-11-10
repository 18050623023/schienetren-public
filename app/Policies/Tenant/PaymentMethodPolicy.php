<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\PaymentMethod;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class PaymentMethodPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:payment_method');
    }

    public function view(authUser $authUser, PaymentMethod $payment_method): bool
    {
        return $this->checkOwnership($authUser, 'view:payment_method', $payment_method);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:payment_method');
    }

    public function update(authUser $authUser, PaymentMethod $payment_method): bool
    {
        return $this->checkOwnership($authUser, 'update:payment_method', $payment_method);
    }

    public function delete(authUser $authUser, PaymentMethod $payment_method): bool
    {
        return $this->checkOwnership($authUser, 'delete:payment_method', $payment_method);
    }

    public function restore(authUser $authUser, PaymentMethod $payment_method): bool
    {
        return $this->checkOwnership($authUser, 'restore:payment_method', $payment_method);
    }

    public function forceDelete(authUser $authUser, PaymentMethod $payment_method): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:payment_method', $payment_method);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:payment_method');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:payment_method');
    }

    public function replicate(authUser $authUser, PaymentMethod $payment_method): bool
    {
        return $this->checkOwnership($authUser, 'replicate:payment_method', $payment_method);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:payment_method');
    }

}
