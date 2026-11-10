<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Tax;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class TaxPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:tax');
    }

    public function view(authUser $authUser, Tax $tax): bool
    {
        return $this->checkOwnership($authUser, 'view:tax', $tax);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:tax');
    }

    public function update(authUser $authUser, Tax $tax): bool
    {
        return $this->checkOwnership($authUser, 'update:tax', $tax);
    }

    public function delete(authUser $authUser, Tax $tax): bool
    {
        return $this->checkOwnership($authUser, 'delete:tax', $tax);
    }

    public function restore(authUser $authUser, Tax $tax): bool
    {
        return $this->checkOwnership($authUser, 'restore:tax', $tax);
    }

    public function forceDelete(authUser $authUser, Tax $tax): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:tax', $tax);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:tax');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:tax');
    }

    public function replicate(authUser $authUser, Tax $tax): bool
    {
        return $this->checkOwnership($authUser, 'replicate:tax', $tax);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:tax');
    }

}
