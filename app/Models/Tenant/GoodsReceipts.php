<?php

namespace App\Models\Tenant;

use App\Enums\TenantFeature;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipts extends Model
{
    

    protected $fillable = [
        'code',
        'purchase_order_id',
        'received_by',
        'date',
        'status',
        'notes',
        'attachment',
    ];

    protected static function booted()
    {
        static::creating(function ($receipt) {
            if (function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER)) {
                throw new \Exception('Fitur Penerimaan Barang (Goods Receipts) tidak aktif pada paket langganan Anda.');
            }
        });

        static::observe(\App\Observers\Tenant\GoodsReceiptsObserver::class);
    }


    public function canDelete()
    {
        return $this->status === 'canceled';
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptsItem::class, 'goods_receipt_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
