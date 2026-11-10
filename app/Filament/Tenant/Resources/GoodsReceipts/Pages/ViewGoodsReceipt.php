<?php

namespace App\Filament\Tenant\Resources\GoodsReceipts\Pages;

use App\Filament\Tenant\Resources\GoodsReceipts\GoodsReceiptsResource;
use App\Models\Tenant\GoodsReceiptsItem;
use App\Models\Tenant\PurchaseOrder;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewGoodsReceipt extends ViewRecord
{
    protected static string $resource = GoodsReceiptsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn ($record) => in_array($record->status, ['draft']) ),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // dd($data);
        $po = PurchaseOrder::with('items')->find($data['purchase_order_id']);


        $data['created_by'] = $po->created_by; ;
        $data['supplier_id'] = $po->supplier_id;
        $data['branch_id'] = $po->branch_id;
        $data['order_date'] = $po->created_at;
        $data['order_notes'] = $po->notes;

        $data['items'] = GoodsReceiptsItem::where('goods_receipt_id', $this->record->id)->get()
            ->map(fn ($item) => [
                        'purchase_order_item_id' => $item->id,
                        'product_variant_id' => $item->purchaseOrderItem->product_variant_id,
                        'product_id' => $item->purchaseOrderItem->product_id,
                        'qty_received' => $item->qty_received,
                        'qty' => $item->qty_received,
                    ])
                    ->toArray() ?? [];

        return $data;
    }



}
