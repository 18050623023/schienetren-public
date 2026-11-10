<?php

namespace App\Filament\Tenant\Resources\GoodsReceipts\Pages;

use App\Filament\Tenant\Resources\GoodsReceipts\GoodsReceiptsResource;
use App\Models\Tenant\GoodsReceiptsItem;
use App\Models\Tenant\PurchaseOrder;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Filament\Facades\Filament;

class CreateGoodsReceipts extends CreateRecord
{
    protected static string $resource = GoodsReceiptsResource::class;


    public function mount(): void
    {
        parent::mount();

        if (request()->has('poid')) {

            $decoded = Hashids::decode(request('poid'));
            $poId = $decoded[0] ?? null;

            if (!$poId) {
                abort(404);
            }

            $receivedQty = GoodsReceiptsItem::select(
                    'purchase_order_item_id',
                    DB::raw('SUM(qty_received) as total_received')
                )
                ->whereHas('goodsReceipt', function ($q) use ($poId) {
                    $q->where('purchase_order_id', $poId)->where('status', 'posted');
                })
                ->groupBy('purchase_order_item_id')
                ->pluck('total_received', 'purchase_order_item_id');


            // $poId = request('poid');
            $po = PurchaseOrder::with('items')->find($poId);
            $items = [];
            if ($po instanceof PurchaseOrder) {
                $items = $po->items->map(function ($item) use ($receivedQty) {
                        $qtyPo = $item->qty;
                        $qtyReceived = $receivedQty[$item->id] ?? 0;

                        $qtySisa = $qtyPo - $qtyReceived;

                        // skip kalau sudah habis
                        if ($qtySisa <= 0) {
                            return null;
                        }

                        return [
                            'purchase_order_item_id' => $item->id,
                            'product_variant_id' => $item->product_variant_id,
                            'product_id' => $item->product_id,
                            'qty_received' => $qtySisa, // default isi sisa
                            'qty' => $qtySisa,
                        ];
                    })
                    ->filter() // buang null
                    ->values()
                    ->toArray();
            }
            // dd($items);
            $this->form->fill([
                'purchase_order_id' => $poId,
                'purchase_order_id_display' => $poId,
                'created_by' => $po->created_by ?? '',
                'branch_id' => $po->branch_id ?? '',
                'supplier_id' => $po->supplier_id ?? '',
                'order_date' => $po->date ?? '',
                'order_notes' => $po->notes ?? '',
                'date' => now(),

                'items' => $items,
            ]);
        }
    }


    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     // $this->items = $data['items'] ?? [];
    //     // unset($data['items']); // penting!
    //     dd($data);

    //     return $data;
    // }

    protected function afterCreate(): void
    {
        $items = $this->form->getState()['items'] ?? [];

        $this->record->items()->insert(
            collect($items)->map(fn ($item) => [
                'goods_receipt_id' => $this->record->id,
                'purchase_order_item_id' => $item['purchase_order_item_id'],
                'qty_received' => $item['qty_received'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray()
        );
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


}
