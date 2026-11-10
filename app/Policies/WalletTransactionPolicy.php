<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\WalletTransaction;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class WalletTransactionPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:wallettransaction');
    }

    public function view(authUser $authUser, WalletTransaction $wallettransaction): bool
    {
        return $this->checkOwnership($authUser, 'view:wallettransaction', $wallettransaction);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:wallettransaction');
    }

    public function update(authUser $authUser, WalletTransaction $wallettransaction): bool
    {
        return $this->checkOwnership($authUser, 'update:wallettransaction', $wallettransaction);
    }

    public function delete(authUser $authUser, WalletTransaction $wallettransaction): bool
    {
        return $this->checkOwnership($authUser, 'delete:wallettransaction', $wallettransaction);
    }

    public function restore(authUser $authUser, WalletTransaction $wallettransaction): bool
    {
        return $this->checkOwnership($authUser, 'restore:wallettransaction', $wallettransaction);
    }

    public function forceDelete(authUser $authUser, WalletTransaction $wallettransaction): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:wallettransaction', $wallettransaction);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:wallettransaction');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:wallettransaction');
    }

    public function replicate(authUser $authUser, WalletTransaction $wallettransaction): bool
    {
        return $this->checkOwnership($authUser, 'replicate:wallettransaction', $wallettransaction);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:wallettransaction');
    }

}
