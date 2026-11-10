<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class RepairItem extends Model
{
    protected $fillable = [
        'repair_device_id',
        'product_variant_id',
        'qty',
        'price',
        'discount_type',
        'discount_value',
        'subtotal_item',
    ];

    protected static function booted(): void
    {
        static::saving(function (RepairItem $item) {
            // Ponytail: Poin 1 - Tagihan Rp 0 (Otomatis) untuk tiket perbaikan dari klaim garansi
            // Jika item ini ditambahkan ke tiket RMA garansi, otomatis diskon 100%
            if ($item->repair_device_id && !$item->exists) {
                $device = RepairDevice::with('repair.warrantyClaim.repairItemWarranty.repairItem')->find($item->repair_device_id);
                if ($device && $device->repair && $device->repair->warrantyClaim) {
                    $coveredVariantId = $device->repair->warrantyClaim->repairItemWarranty?->repairItem?->product_variant_id;
                    
                    // Hanya otomatis diskon 100% jika barang yang dimasukkan ADALAH barang yang sama dengan yang diklaim garansi
                    if ($coveredVariantId && $item->product_variant_id == $coveredVariantId) {
                        $item->discount_type = 'percentage';
                        $item->discount_value = 100;
                    }
                }
            }

            $qty = $item->qty ?? 1;
            $price = $item->price ?? 0;
            $discount = $item->discount_value ?? 0;
            
            if ($item->discount_type === 'percentage') {
                $discount = ($price * $qty) * ($discount / 100);
            }
            
            $item->qty = $qty;
            $item->price = $price;
            $item->subtotal_item = ($qty * $price) - $discount;
        });

        static::deleting(function (RepairItem $item) {
            $item->stockMovements()->each(function ($movement) {
                $movement->delete();
            });
        });

        static::saved(function (RepairItem $item) {
            self::updateParentInvoice($item);
        });

        static::deleted(function (RepairItem $item) {
            self::updateParentInvoice($item);
        });
    }

    protected static function updateParentInvoice(RepairItem $item): void
    {
        $device = RepairDevice::with('repair.repairDevices.repairItems')->find($item->repair_device_id);
        
        if (!$device || !$device->repair) {
            return;
        }

        $repair = $device->repair;
        $hasItems = $repair->repairDevices->contains(fn($d) => $d->repairItems->isNotEmpty());

        if ($hasItems) {
            $total = $repair->repairDevices->flatMap->repairItems->sum('subtotal_item');
            $invoice = \App\Models\Tenant\RepairInvoice::findOrCreateForRepair($repair);
            $invoice->recalculateTotals($total);
        } else {
            if ($repair->repairInvoice) {
                if ($repair->repairInvoice->paid_amount == 0) {
                    $repair->repairInvoice()->delete();
                } else {
                    $repair->repairInvoice->recalculateTotals(0);
                }
            }
        }
    }

    public function repairDevice()
    {
        return $this->belongsTo(RepairDevice::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function repairItemWarranties()
    {
        return $this->hasMany(RepairItemWarranty::class);
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }

    public function stockMovement()
    {
        return $this->morphOne(StockMovement::class, 'source')->latestOfMany();
    }
}
