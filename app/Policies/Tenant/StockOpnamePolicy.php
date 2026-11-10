<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\User as authUser;
use App\Models\Tenant\StockOpname;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class StockOpnamePolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:stock_opname');
    }

    public function view(authUser $authUser, StockOpname $stock_opname): bool
    {
        if ($stock_opname->type === 'regular' && function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'view:stock_opname', $stock_opname);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:stock_opname');
    }

    public function update(authUser $authUser, StockOpname $stock_opname): bool
    {
        if ($stock_opname->type === 'regular' && function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'update:stock_opname', $stock_opname);
    }

    public function delete(authUser $authUser, StockOpname $stock_opname): bool
    {
        if ($stock_opname->type === 'regular' && function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'delete:stock_opname', $stock_opname);
    }

    public function restore(authUser $authUser, StockOpname $stock_opname): bool
    {
        if ($stock_opname->type === 'regular' && function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'restore:stock_opname', $stock_opname);
    }

    public function forceDelete(authUser $authUser, StockOpname $stock_opname): bool
    {
        if ($stock_opname->type === 'regular' && function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'forceDelete:stock_opname', $stock_opname);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        if (function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'forceDeleteAny:stock_opname');
    }

    public function restoreAny(authUser $authUser): bool
    {
        if (function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'restoreAny:stock_opname');
    }

    public function replicate(authUser $authUser, StockOpname $stock_opname): bool
    {
        if ($stock_opname->type === 'regular' && function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'replicate:stock_opname', $stock_opname);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:stock_opname');
    }
}
