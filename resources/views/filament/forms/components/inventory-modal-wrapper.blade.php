@php
    // Wrapper untuk memastikan event didengar oleh Livewire komponen utama (form)
@endphp
<div
    x-data
    @select-inventory-item.window="
        let targetPath = $event.detail.targetStatePath;
        let item = $event.detail.item;
        
        // Memastikan state path mengarah ke property 'data' jika form menggunakan prefix 'data'
        if ($wire.data !== undefined && !targetPath.startsWith('data.')) {
            targetPath = 'data.' + targetPath;
        }

        let currentItems = $wire.get(targetPath) || [];

        let newItemKey = 'item-' + Math.random().toString(36).substring(2, 9);
        if (Array.isArray(currentItems)) {
            currentItems.push(item);
        } else if (typeof currentItems === 'object' && currentItems !== null) {
            currentItems[newItemKey] = item;
        } else {
            currentItems = [item];
        }

        $wire.set(targetPath, currentItems);
        
        // Menutup modal action komponen form
        if (typeof $wire.unmountFormComponentAction === 'function') {
            $wire.unmountFormComponentAction();
        } else {
            $dispatch('close-modal', { id: '{{ $this->getId() }}-form-component-action' });
        }
    "
>
    @livewire(\App\Livewire\InventoryStockPicker::class, [
        'targetStatePath' => $targetStatePath,
        'branchId' => $branchId,
    ])
</div>
