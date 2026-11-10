<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Pages\Concerns;

use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\ProductVariant;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

trait HandlesSerialSelection
{
    #[On('product-variant-selected')]
    public function handleProductVariantSelected($recordId, $statePath)
    {
        $variant = ProductVariant::with(['product.brand'])->find($recordId);
        if (!$variant) return;

        // statePath is 'data.items.uuid.pilih_produk'
        $basePath = str_replace('.pilih_produk', '', $statePath);

        // Check duplicate variant in other repeater items
        $items = data_get($this, 'data.items', []);
        $currentRowKey = last(explode('.', $basePath));

        foreach ($items as $key => $item) {
            if ($key !== $currentRowKey && isset($item['product_variant_id']) && $item['product_variant_id'] == $recordId) {
                Notification::make()
                    ->title('Varian ini sudah dipilih di baris lain')
                    ->warning()
                    ->send();
                return;
            }
        }

        $displayName = trim(($variant->product?->brand?->name ?? '') . ' ' . ($variant->product?->name ?? '') . ($variant->name ? ' - ' . $variant->name : ''));

        data_set($this, $basePath . '.product_id', $variant->product_id);
        data_set($this, $basePath . '.product_variant_id', $variant->id);
        data_set($this, $basePath . '.product_display_name', $displayName);
        data_set($this, $basePath . '.sku', $variant->sku);
        data_set($this, $basePath . '.has_sn', (bool) $variant->product?->has_sn);
        data_set($this, $basePath . '.itemSerials', []);
        data_set($this, $basePath . '.qty_sent', $variant->product?->has_sn ? 0 : 1);

        $this->unmountAction();
    }

    #[On('record-selected')]
    public function handleSerialSelected($recordId, $statePath)
    {
        $serial = ProductSerial::find($recordId);
        if (!$serial) return;

        // statePath is 'data.items.uuid.pilih_serial' since the action is inside the repeater
        // We need to extract the base repeater item path: 'data.items.uuid'
        $basePath = str_replace('.pilih_serial', '', $statePath);
        
        $itemSerialsPath = $basePath . '.itemSerials';
        
        $currentSerials = data_get($this, $itemSerialsPath, []);
        
        // Prevent duplicate serial
        foreach ($currentSerials as $s) {
            if (isset($s['product_serial_id']) && $s['product_serial_id'] == $recordId) {
                // optional: add notification
                Notification::make()
                    ->title('Serial sudah dipilih')
                    ->danger()
                    ->send();
                return;
            }
        }

        $newKey = (string) str()->uuid();
        $currentSerials[$newKey] = [
            'product_serial_id' => $serial->id,
            'serial_number' => $serial->serial_number,
        ];
        
        data_set($this, $itemSerialsPath, $currentSerials);
        
        // Update qty_sent automatically based on serials count
        $qtyPath = $basePath . '.qty_sent';
        data_set($this, $qtyPath, count($currentSerials));
        // dd($statePath);
        // $this->dispatch('close-modal', id: "{$statePath}-action-0");
        $this->unmountAction();
    }
}

