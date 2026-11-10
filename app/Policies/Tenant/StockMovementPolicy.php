<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\StockMovement;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class StockMovementPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:stock_movement');
    }

    public function view(authUser $authUser, StockMovement $stock_movement): bool
    {
        return $this->checkOwnership($authUser, 'view:stock_movement', $stock_movement);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:stock_movement');
    }

    public function update(authUser $authUser, StockMovement $stock_movement): bool
    {
        return $this->checkOwnership($authUser, 'update:stock_movement', $stock_movement);
    }

    public function delete(authUser $authUser, StockMovement $stock_movement): bool
    {
        return $this->checkOwnership($authUser, 'delete:stock_movement', $stock_movement);
    }

    public function restore(authUser $authUser, StockMovement $stock_movement): bool
    {
        return $this->checkOwnership($authUser, 'restore:stock_movement', $stock_movement);
    }

    public function forceDelete(authUser $authUser, StockMovement $stock_movement): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:stock_movement', $stock_movement);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:stock_movement');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:stock_movement');
    }

    public function replicate(authUser $authUser, StockMovement $stock_movement): bool
    {
        return $this->checkOwnership($authUser, 'replicate:stock_movement', $stock_movement);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:stock_movement');
    }

}
