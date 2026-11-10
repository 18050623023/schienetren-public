<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_variant_id',
        'qty',
        'qty_received',
        'qty_pending',
        'price',
        'subtotal',
    ];


    public function canDelete()
    {
        return $this->purchaseOrder->status === 'canceled';
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function goodsReceiptItems()
    {
        return $this->hasMany(GoodsReceiptsItem::class);
    }
}
