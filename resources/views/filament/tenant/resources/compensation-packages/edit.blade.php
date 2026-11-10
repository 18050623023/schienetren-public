<x-filament-panels::page>
    <form wire:submit="save">

        <div class="flex items-end gap-3">
            <div class="flex-1">
                {{ $this->form }}
            </div>

            {{-- <div class="pb-1">
                {{ $this->getSaveFormAction() }}
            </div> --}}
        </div>
        {{-- Form --}}
        {{-- {{ $this->form }} --}}

        {{-- Save di bawah form --}}
        {{-- <div class="mt-4 flex justify-end"> --}}
            {{-- {{ $this->getSaveFormAction() }} --}}
        {{-- </div> --}}

    </form>

    {{-- Relation Manager --}}
    {{ $this->content }}
</x-filament-panels::page>
