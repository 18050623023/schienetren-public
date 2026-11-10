<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptsItem extends Model
{
    

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'qty_received',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipts::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

}
