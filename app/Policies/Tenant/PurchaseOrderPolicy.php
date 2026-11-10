<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\PurchaseOrder;
use App\Enums\TenantFeature;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class PurchaseOrderPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:purchase_order');
    }

    public function view(authUser $authUser, PurchaseOrder $purchase_order): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'view:purchase_order', $purchase_order);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:purchase_order');
    }

    public function update(authUser $authUser, PurchaseOrder $purchase_order): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'update:purchase_order', $purchase_order);
    }

    public function delete(authUser $authUser, PurchaseOrder $purchase_order): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'delete:purchase_order', $purchase_order);
    }

    public function restore(authUser $authUser, PurchaseOrder $purchase_order): bool
    {
        return $this->checkOwnership($authUser, 'restore:purchase_order', $purchase_order);
    }

    public function forceDelete(authUser $authUser, PurchaseOrder $purchase_order): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:purchase_order', $purchase_order);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:purchase_order');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:purchase_order');
    }

    public function replicate(authUser $authUser, PurchaseOrder $purchase_order): bool
    {
        return $this->checkOwnership($authUser, 'replicate:purchase_order', $purchase_order);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:purchase_order');
    }

}
