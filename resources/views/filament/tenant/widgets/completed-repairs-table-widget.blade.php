<x-filament-widgets::widget class="fi-wi-table space-y-4">
    <div class="flex items-center gap-2">
        <x-filament::tabs label="Filter Status Riwayat">
            <x-filament::tabs.item
                :active="$statusTab === 'all'"
                wire:click="$set('statusTab', 'all')"
                :badge="$this->getDeviceCount('all')"
            >
                Semua Riwayat
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$statusTab === 'done'"
                wire:click="$set('statusTab', 'done')"
                :badge="$this->getDeviceCount('done')"
                badge-color="success"
            >
                Done (Selesai)
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$statusTab === 'not_possible'"
                wire:click="$set('statusTab', 'not_possible')"
                :badge="$this->getDeviceCount('not_possible')"
                badge-color="danger"
            >
                Not Possible
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$statusTab === 'cancel'"
                wire:click="$set('statusTab', 'cancel')"
                :badge="$this->getDeviceCount('cancel')"
                badge-color="danger"
            >
                Cancel (Dibatalkan)
            </x-filament::tabs.item>
        </x-filament::tabs>
    </div>

    {{ $this->table }}
</x-filament-widgets::widget>
