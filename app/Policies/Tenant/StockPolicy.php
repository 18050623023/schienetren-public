<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Stock;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class StockPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:stock');
    }

    public function view(authUser $authUser, Stock $stock): bool
    {
        return $this->checkOwnership($authUser, 'view:stock', $stock);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:stock');
    }

    public function update(authUser $authUser, Stock $stock): bool
    {
        return $this->checkOwnership($authUser, 'update:stock', $stock);
    }

    public function delete(authUser $authUser, Stock $stock): bool
    {
        return $this->checkOwnership($authUser, 'delete:stock', $stock);
    }

    public function restore(authUser $authUser, Stock $stock): bool
    {
        return $this->checkOwnership($authUser, 'restore:stock', $stock);
    }

    public function forceDelete(authUser $authUser, Stock $stock): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:stock', $stock);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:stock');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:stock');
    }

    public function replicate(authUser $authUser, Stock $stock): bool
    {
        return $this->checkOwnership($authUser, 'replicate:stock', $stock);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:stock');
    }

}
