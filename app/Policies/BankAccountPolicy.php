<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\BankAccount;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class BankAccountPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:bankaccount');
    }

    public function view(authUser $authUser, BankAccount $bankaccount): bool
    {
        return $this->checkOwnership($authUser, 'view:bankaccount', $bankaccount);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:bankaccount');
    }

    public function update(authUser $authUser, BankAccount $bankaccount): bool
    {
        return $this->checkOwnership($authUser, 'update:bankaccount', $bankaccount);
    }

    public function delete(authUser $authUser, BankAccount $bankaccount): bool
    {
        return $this->checkOwnership($authUser, 'delete:bankaccount', $bankaccount);
    }

    public function restore(authUser $authUser, BankAccount $bankaccount): bool
    {
        return $this->checkOwnership($authUser, 'restore:bankaccount', $bankaccount);
    }

    public function forceDelete(authUser $authUser, BankAccount $bankaccount): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:bankaccount', $bankaccount);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:bankaccount');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:bankaccount');
    }

    public function replicate(authUser $authUser, BankAccount $bankaccount): bool
    {
        return $this->checkOwnership($authUser, 'replicate:bankaccount', $bankaccount);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:bankaccount');
    }

}
