<x-filament-panels::page>
    <form wire:submit="save">

        <div class="flex items-end gap-3">
            <div class="flex-1">
                {{ $this->form }}
            </div>
        </div>
    </form>

    {{-- Relation Manager --}}
    {{ $this->content }}
</x-filament-panels::page>
