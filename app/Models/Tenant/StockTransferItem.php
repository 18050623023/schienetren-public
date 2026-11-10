<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'product_variant_id',
        'qty_sent',
        'qty_received',
    ];

    protected static function booted()
    {
        // Cegah update field krusial jika sedang in_transit
        static::updating(function ($item) {
            $transfer = $item->stockTransfer;
            if ($transfer && $transfer->status === 'in_transit') {
                // $item->isDirty() mengecek apakah kolom-kolom ini mengalami perubahan nilai
                if ($item->isDirty(['product_id', 'product_variant_id', 'qty_sent', 'stock_transfer_id'])) {
                    throw new \Exception('Hanya Qty Diterima yang boleh diubah saat mutasi dalam status pengiriman (In Transit).');
                }
            }
        });

        // Cegah penghapusan baris item jika status sudah terkirim / selesai
        static::deleting(function ($item) {
            $transfer = $item->stockTransfer;
            if ($transfer && in_array($transfer->status, ['in_transit', 'completed', 'received'])) {
                throw new \Exception('Tidak bisa menghapus item mutasi yang sudah dalam pengiriman atau selesai.');
            }
        });
    }

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function itemSerials(): HasMany
    {
        return $this->hasMany(StockTransferItemSerial::class);
    }
}
