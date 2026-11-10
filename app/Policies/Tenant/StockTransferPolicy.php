<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\StockTransfer;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class StockTransferPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::STOCK_TRANSFER)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:stock_transfer');
    }

    public function view(authUser $authUser, StockTransfer $stock_transfer): bool
    {
        return $this->checkOwnership($authUser, 'view:stock_transfer', $stock_transfer);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::STOCK_TRANSFER)) {
            return false;
        }

        if (\App\Models\Tenant\Branch::where('is_active', true)->count() <= 1) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:stock_transfer');
    }

    public function update(authUser $authUser, StockTransfer $stock_transfer): bool
    {
        return $this->checkOwnership($authUser, 'update:stock_transfer', $stock_transfer);
    }

    public function delete(authUser $authUser, StockTransfer $stock_transfer): bool
    {
        return $this->checkOwnership($authUser, 'delete:stock_transfer', $stock_transfer);
    }

    public function restore(authUser $authUser, StockTransfer $stock_transfer): bool
    {
        return $this->checkOwnership($authUser, 'restore:stock_transfer', $stock_transfer);
    }

    public function forceDelete(authUser $authUser, StockTransfer $stock_transfer): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:stock_transfer', $stock_transfer);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:stock_transfer');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:stock_transfer');
    }

    public function replicate(authUser $authUser, StockTransfer $stock_transfer): bool
    {
        return $this->checkOwnership($authUser, 'replicate:stock_transfer', $stock_transfer);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:stock_transfer');
    }

}
