<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\Invoice;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class InvoicePolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:invoice');
    }

    public function view(authUser $authUser, Invoice $invoice): bool
    {
        return $this->checkOwnership($authUser, 'view:invoice', $invoice);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:invoice');
    }

    public function update(authUser $authUser, Invoice $invoice): bool
    {
        return $this->checkOwnership($authUser, 'update:invoice', $invoice);
    }

    public function delete(authUser $authUser, Invoice $invoice): bool
    {
        return $this->checkOwnership($authUser, 'delete:invoice', $invoice);
    }

    public function restore(authUser $authUser, Invoice $invoice): bool
    {
        return $this->checkOwnership($authUser, 'restore:invoice', $invoice);
    }

    public function forceDelete(authUser $authUser, Invoice $invoice): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:invoice', $invoice);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:invoice');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:invoice');
    }

    public function replicate(authUser $authUser, Invoice $invoice): bool
    {
        return $this->checkOwnership($authUser, 'replicate:invoice', $invoice);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:invoice');
    }

}
