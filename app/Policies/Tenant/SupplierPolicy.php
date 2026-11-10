<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Supplier;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class SupplierPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:supplier');
    }

    public function view(authUser $authUser, Supplier $supplier): bool
    {
        return $this->checkOwnership($authUser, 'view:supplier', $supplier);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:supplier');
    }

    public function update(authUser $authUser, Supplier $supplier): bool
    {
        return $this->checkOwnership($authUser, 'update:supplier', $supplier);
    }

    public function delete(authUser $authUser, Supplier $supplier): bool
    {
        return $this->checkOwnership($authUser, 'delete:supplier', $supplier);
    }

    public function restore(authUser $authUser, Supplier $supplier): bool
    {
        return $this->checkOwnership($authUser, 'restore:supplier', $supplier);
    }

    public function forceDelete(authUser $authUser, Supplier $supplier): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:supplier', $supplier);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:supplier');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:supplier');
    }

    public function replicate(authUser $authUser, Supplier $supplier): bool
    {
        return $this->checkOwnership($authUser, 'replicate:supplier', $supplier);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:supplier');
    }

}
