<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\PaymentConfirmation;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class PaymentConfirmationPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:paymentconfirmation');
    }

    public function view(authUser $authUser, PaymentConfirmation $paymentconfirmation): bool
    {
        return $this->checkOwnership($authUser, 'view:paymentconfirmation', $paymentconfirmation);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:paymentconfirmation');
    }

    public function update(authUser $authUser, PaymentConfirmation $paymentconfirmation): bool
    {
        return $this->checkOwnership($authUser, 'update:paymentconfirmation', $paymentconfirmation);
    }

    public function delete(authUser $authUser, PaymentConfirmation $paymentconfirmation): bool
    {
        return $this->checkOwnership($authUser, 'delete:paymentconfirmation', $paymentconfirmation);
    }

    public function restore(authUser $authUser, PaymentConfirmation $paymentconfirmation): bool
    {
        return $this->checkOwnership($authUser, 'restore:paymentconfirmation', $paymentconfirmation);
    }

    public function forceDelete(authUser $authUser, PaymentConfirmation $paymentconfirmation): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:paymentconfirmation', $paymentconfirmation);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:paymentconfirmation');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:paymentconfirmation');
    }

    public function replicate(authUser $authUser, PaymentConfirmation $paymentconfirmation): bool
    {
        return $this->checkOwnership($authUser, 'replicate:paymentconfirmation', $paymentconfirmation);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:paymentconfirmation');
    }

}
