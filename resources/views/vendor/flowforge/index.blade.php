@php use Filament\Support\Facades\FilamentAsset; @endphp
@props(['columns', 'config'])

<div
    class="w-full h-full flex flex-col relative"
    x-load
    x-load-src="{{ FilamentAsset::getAlpineComponentSrc('flowforge', package: 'relaticle/flowforge') }}"
    x-data="flowforge({
        state: {
            columns: @js($columns),
            titleField: '{{ $config['recordTitleAttribute'] }}',
            columnField: '{{ $config['columnIdentifierAttribute'] }}',
            cardLabel: '{{ $config['cardLabel'] }}',
            pluralCardLabel: '{{ $config['pluralCardLabel'] }}',
        }
    })"
>

    @include('flowforge::components.filters')

    <!-- Board Content -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden h-full">
        <div class="flex flex-row flex-wrap h-full gap-5 pb-4">
            @foreach($columns as $columnId => $column)
                <x-flowforge::column
                    :columnId="$columnId"
                    :column="$column"
                    :config="$config"
                    wire:key="column-{{ $columnId }}"
                />
            @endforeach
        </div>
    </div>

    <x-filament-actions::modals/>
</div>
