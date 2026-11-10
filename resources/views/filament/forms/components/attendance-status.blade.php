<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
@php

    $state = $field->getState();
    // $state = $getState();
    // dd($getState());

    $color = match ($state) {
        'present' => 'success',
        'late'    => 'danger',
        'early'   => 'warning',
        default   => 'gray',
    };
@endphp
    <x-filament::badge :color="$color"

        class="[padding-block:calc(var(--spacing)*2.5)]">
        {{ Str::ucfirst($state?: 'Belum Absen') }}
    </x-filament::badge>

</x-dynamic-component>
