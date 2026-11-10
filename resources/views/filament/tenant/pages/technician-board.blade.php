<x-filament-panels::page>
    <div class="border-b border-gray-200 dark:border-gray-800 pb-2 mb-4">
        <x-filament::tabs label="Mode Tampilan">
            <x-filament::tabs.item
                :active="$activeTab === 'board'"
                wire:click="$set('activeTab', 'board')"
                icon="heroicon-o-view-columns"
            >
                Papan Kerja Aktif (Kanban)
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'history'"
                wire:click="$set('activeTab', 'history')"
                icon="heroicon-o-archive-box"
            >
                Riwayat Selesai & Batal (Tabel)
            </x-filament::tabs.item>
        </x-filament::tabs>
    </div>

    <div x-show="$wire.activeTab === 'board'" class="h-[calc(100vh-14rem)]">
        {{ $this->board }}
    </div>

    <div x-show="$wire.activeTab === 'history'" x-cloak>
        @livewire(\App\Filament\Tenant\Widgets\CompletedRepairsTableWidget::class)
    </div>
</x-filament-panels::page>
