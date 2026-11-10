<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\Actions;

use App\Enums\TenantFeature;
use App\Models\Tenant\PurchaseOrder;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

class PurchaseOrderActionStatus
{
    public static function confirmAction(PurchaseOrder $purchase): void
    {
        abort_unless(tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER), 403, 'Fitur Purchase Order tidak aktif pada paket langganan Anda.');

        $purchase->status = 'confirmed';
        $purchase->confirmed_at = now();
        $purchase->confirmed_by = Filament::auth()->user()->id;
        $purchase->save();

        Notification::make()
            ->title(fn() => 'Status Purchase Order diubah menjadi CONFIRMED')
            ->success()
            ->send();
    }

    public static function cancelAction(PurchaseOrder $purchase): void
    {
        abort_unless(tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER), 403, 'Fitur Purchase Order tidak aktif pada paket langganan Anda.');
        $purchase->status = 'canceled';
        $purchase->canceled_at = now();
        $purchase->canceled_by = Filament::auth()->user()->id;
        $purchase->save();

        Notification::make()
            ->title(fn() => 'Status Purchase Order diubah menjadi CANCELED')
            ->success()
            ->send();
    }
}
