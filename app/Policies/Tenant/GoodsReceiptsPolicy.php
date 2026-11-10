<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\GoodsReceipts;
use App\Enums\TenantFeature;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class GoodsReceiptsPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:goods_receipts');
    }

    public function view(authUser $authUser, GoodsReceipts $goods_receipts): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'view:goods_receipts', $goods_receipts);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:goods_receipts');
    }

    public function update(authUser $authUser, GoodsReceipts $goods_receipts): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'update:goods_receipts', $goods_receipts);
    }

    public function delete(authUser $authUser, GoodsReceipts $goods_receipts): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'delete:goods_receipts', $goods_receipts);
    }

    public function restore(authUser $authUser, GoodsReceipts $goods_receipts): bool
    {
        return $this->checkOwnership($authUser, 'restore:goods_receipts', $goods_receipts);
    }

    public function forceDelete(authUser $authUser, GoodsReceipts $goods_receipts): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:goods_receipts', $goods_receipts);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:goods_receipts');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:goods_receipts');
    }

    public function replicate(authUser $authUser, GoodsReceipts $goods_receipts): bool
    {
        return $this->checkOwnership($authUser, 'replicate:goods_receipts', $goods_receipts);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:goods_receipts');
    }

}
