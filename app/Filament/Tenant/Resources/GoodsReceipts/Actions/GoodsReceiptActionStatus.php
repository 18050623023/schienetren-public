<?php

namespace App\Filament\Tenant\Resources\GoodsReceipts\Actions;

use App\Enums\TenantFeature;
use App\Models\Tenant\GoodsReceipts;
use App\Models\Tenant\GoodsReceiptsItem;
use App\Models\Tenant\PurchaseOrder;
use App\Models\Tenant\Stock;
use App\Models\Tenant\StockMovement;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class GoodsReceiptActionStatus
{

    public static function confirmAction(GoodsReceipts $gr): void
    {
        abort_unless(tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER), 403, 'Fitur Purchase Order tidak aktif pada paket langganan Anda.');

        DB::transaction(function () use ($gr) {

            if ($gr->status === 'posted') {
                abort(403, 'dokumen sudah dipost');
            }

            $gr->load([
                'items.purchaseOrderItem',
                // 'items.productVariant',
                'purchaseOrder.items'
            ]);

            foreach ($gr->items as $item) {

                $poItem = $item->purchaseOrderItem;

                // 🔍 validasi real received
                $realReceived = GoodsReceiptsItem::query()
                    ->where('purchase_order_item_id', $poItem->id)
                    ->whereHas('goodsReceipt', fn ($q) => $q->where('status', 'posted'))
                    ->sum('qty_received');

                if (($realReceived + $item->qty_received) > $poItem->qty) {
                    abort(403, 'qty received melebihi qty po');
                }

                // 💾 update PO item
                $poItem->qty_received = $realReceived + $item->qty_received;
                $poItem->qty_pending = $poItem->qty - $poItem->qty_received;
                $poItem->save();

                // 📦 STOCK MOVEMENT (WAJIB)
                StockMovement::create([
                    'branch_id' => $gr->purchaseOrder->branch_id,
                    'product_id' => $item->purchaseOrderItem->product_id,
                    'product_variant_id' => $item->purchaseOrderItem->product_variant_id,
                    'direction' => 'in',
                    'quantity' => $item->qty_received,
                    'note' => 'GR #' . $gr->code,
                    'source_type' => 'purchase',
                    'source_id' => $gr->id,
                ]);

                // 📊 UPDATE STOCK (cache)
                $stock = Stock::firstOrNew([
                    'branch_id' => $gr->purchaseOrder->branch_id,
                    'product_id' => $item->purchaseOrderItem->product_id,
                    'product_variant_id' => $item->purchaseOrderItem->product_variant_id,
                ],
                [
                    'stock' => 0,
                ]);


                if (!$stock->exists) {
                    $stock->stock = 0;
                }

                $stock->stock += $item->qty_received;
                $stock->save();
            }

            $po = $gr->purchaseOrder;

            // 🔄 update status PO
            $po->load('items');

            if ($po->items->every(fn ($i) => $i->qty_pending == 0)) {
                $po->status = 'completed';
            } else {
                $po->status = 'partially';
            }

            $po->save();

            // ✅ update GR
            $gr->status = 'posted';
            $gr->posted_at = now();
            $gr->posted_by = Auth::id() ?? $gr->received_by;
            $gr->save();
        });

        Notification::make()
            ->title('Goods Receipt berhasil diposting')
            ->success()
            ->send();

    }

    // public static function confirmAction(GoodsReceipts $gr): void
    // {
    //     $qtyReceived = GoodsReceiptsItem::where('purchase_order_item_id', $poItem->id)
    //         ->whereHas('goodsReceipt', fn ($q) => $q->where('status', 'posted'))
    //         ->sum('qty');

    //     dd($qtyReceived);

    //     // $gr->status = 'posted';
    //     // $gr->posted_at = now();
    //     // $gr->posted_by = Filament::auth()->user()->id;
    //     // $gr->save();

    //     Notification::make()
    //         ->title(fn() => 'Status Purchase Order diubah menjadi CONFIRMED')
    //         ->success()
    //         ->send();
    // }

    public static function cancelAction(GoodsReceipts $gr): void
    {
        abort_unless(tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER), 403, 'Fitur Purchase Order tidak aktif pada paket langganan Anda.');

        $gr->status = 'canceled';
        $gr->canceled_at = now();
        $gr->canceled_by = Auth::id() ?? $gr->received_by;
        $gr->save();

        Notification::make()
            ->title('Status Goods Receipt diubah menjadi CANCELED')
            ->success()
            ->send();
    }
}
