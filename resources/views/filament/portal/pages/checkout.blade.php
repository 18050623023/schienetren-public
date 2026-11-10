<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-4 flex justify-end gap-x-4">
            <x-filament::button type="submit" size="lg">
                Proses Pembayaran
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
