<x-filament::modal id="distance-modal"
    icon="heroicon-o-exclamation-triangle"
    icon-color="danger"
>
    {{-- Modal content --}}
    <x-slot name="heading">
        Outside attendance radius
    </x-slot>

    <x-slot name="description">
        Anda berada diluar radius absensi <br>
        {{-- Sejauh <b>{{ (/100) ?? '-' }} meter</b> --}}
        Sejauh <b>{{ $formatDistance(data_get($getState(), 'distance'))}} meter</b>
    </x-slot>
</x-filament::modal>

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        {{-- x-data="{ state: $wire.$entangle(@js($getStatePath())) }"
        {{ $getExtraAttributeBag() }} --}}
    >
        {{-- Interact with the `state` property in Alpine.js --}}
    </div>

    <div
        x-data="geofenceMap({
            wire: $wire,
            statePath:'{{ $getStatePath() }}',
            lat: $wire.entangle(@js($rootStatePath.'.' . $latitudeField)),
            lng: $wire.entangle(@js($rootStatePath.'.' . $longitudeField)),
            radius: (typeof $wire.data !== 'undefined' && 'attendance_radius' in $wire.data)
                    ? $wire.entangle('data.attendance_radius')
                    : 0,
            {{-- distance: (typeof $wire.data !== 'undefined' && @js('data.' . $distanceField) in $wire.data)
                    ? $wire.entangle(@js('data.' . $distanceField))
                    : 0, --}}
            config: @js($config),
            permanentCircle: @json($permanentCircle),
            latCircle: '{{ $latCircle }}',
            lngCircle: '{{ $lngCircle }}',
            radCircle: '{{ $radCircle }}',

        })"
    >
        <div

            wire:ignore
            x-ref="map"
            style="height:{{ $getConfig()['height'] ?? '400' }}px"
        ></div>
    </div>

</x-dynamic-component>
