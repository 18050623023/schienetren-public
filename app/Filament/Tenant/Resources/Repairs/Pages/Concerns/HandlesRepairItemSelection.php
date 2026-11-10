<?php

namespace App\Filament\Tenant\Resources\Repairs\Pages\Concerns;

use App\Models\Tenant\ProductSerial;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

trait HandlesRepairItemSelection
{
    #[On('repair-item-selected')]
    public function handleRepairItemSelected($recordId, $statePath, $item = [])
    {
        $targetPath = $statePath;
        if (!str_starts_with($targetPath, 'data.') && !str_starts_with($targetPath, 'mounted')) {
            $targetPath = 'data.' . $targetPath;
        }
        $currentItems = data_get($this, $targetPath, []);
        // dd($currentItems);
        $hasSn = (bool) ($item['has_sn'] ?? false);
        $price = (float) ($item['price'] ?? 0);
        $variantId = $item['product_variant_id'] ?? $recordId;

        // 1. Jika NON-SN dan sudah ada di repeater -> Tambah QTY pada baris yang sudah ada
        if (!$hasSn) {
            foreach ($currentItems as $key => $existingItem) {
                if (($existingItem['product_variant_id'] ?? null) == $variantId) {
                    $newQty = (int) ($existingItem['qty'] ?? 1) + 1;
                    // $maxStock = (int) ($existingItem['max_stock'] ?? $item['max_stock'] ?? 9999);
                    $maxStock = $existingItem['max_stock'] ?? $item['max_stock'] ?? null;
                    
                    // if ($newQty > $maxStock) {
                    if ($maxStock !== null && $newQty > (int) $maxStock) {
                        Notification::make()
                            ->title('Stok tidak mencukupi')
                            ->body("Stok maksimal adalah {$maxStock}")
                            ->warning()
                            ->send();
                        $this->unmountAction();
                        return;
                    }
                    
                    $currentItems[$key]['qty'] = $newQty;
                    $currentItems[$key]['subtotal'] = $price * $newQty;
                    $currentItems[$key]['subtotal_item'] = $price * $newQty;
                    data_set($this, $targetPath, $currentItems);
                    $this->unmountAction();
                    return;
                }
            }
        }

        $uuid = (string) str((string) \Illuminate\Support\Str::uuid());
        $currentItems[$uuid] = [
            'product_id' => $item['product_id'] ?? null,
            'product_variant_id' => $item['product_variant_id'] ?? $recordId,
            'name' => $item['name'] ?? '',
            'price' => $price,
            'warranty' => $item['warranty'] ?? 'Tidak ada',
            'has_sn' => $hasSn,
            'qty' => 1,
            'sn' => $hasSn ? null : 'Non-SN',
            'product_serial_id' => null,
            'subtotal' => $price,
            'subtotal_item' => $price,
            'max_stock' => $item['max_stock'] ?? null,
        ];
        
        data_set($this, $targetPath, $currentItems);
        
        // dd(data_get($this, $targetPath, []));
        // dd(data_get($this, 'data', []));
        $this->unmountAction();
    }

    #[On('repair-serial-selected')]
    public function handleRepairSerialSelected($recordId, $statePath, $item = [])
    {
        $serial = ProductSerial::find($recordId);
        if (!$serial) {
            return;
        }

        $basePath = preg_replace('/(\.sn|\.select_serial)+$/', '', $statePath);
        if (!str_starts_with($basePath, 'data.') && !str_starts_with($basePath, 'mountedActions.')) {
            $basePath = 'data.' . $basePath;
        }

        $currentRowSerialId = data_get($this, $basePath . '.product_serial_id');
        if ($currentRowSerialId == $recordId) {
            
            Notification::make()
                ->title('Serial Number sudah dipilih')
                ->warning()
                ->send();
            $this->unmountAction();

            return;
        }

        // Check duplicate across all items in form data (including uncommitted modal state)
        $formData = [
            data_get($this, 'data', []),
            data_get($this, 'mountedFormComponentActionsData', []),
            data_get($this, 'mountedTableActionsData', []),
            data_get($this, 'mountedActionsData', []),
            data_get($this, 'mountedActions', []),
            data_get($this, 'mountedTableActions', []),
            data_get($this, 'mountedFormComponentActions', []),
        ];
        // dd($formData);

        $isDuplicate = false;

        $checkDuplicates = function ($array) use (&$checkDuplicates, $recordId, &$isDuplicate) {
            foreach ($array as $val) {
                if (is_array($val)) {
                    if (isset($val['product_serial_id']) && $val['product_serial_id'] == $recordId) {
                        $isDuplicate = true;
                        return;
                    }
                    $checkDuplicates($val);
                }
            }
        };
        $checkDuplicates($formData);

        if ($isDuplicate) {
            Notification::make()
                ->title('Serial Number sudah dipilih pada baris lain')
                ->warning()
                ->send();

            $this->unmountAction();
            
            return;
        }

        data_set($this, $basePath . '.product_serial_id', $serial->id);
        data_set($this, $basePath . '.sn', $serial->serial_number);
        // dd(data_get($this, $basePath, []));

        $this->unmountAction();
    }
}
