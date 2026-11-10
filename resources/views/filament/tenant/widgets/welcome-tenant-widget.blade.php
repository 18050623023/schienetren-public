<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Widget content --}}

    <div class="">
        Selamat Datang
    </div>


    <div class="text-xl font-medium text-black dark:text-white">
        {{ tenancy()->tenant?->business_name }}
    </div>


    <p>ID : {{ tenancy()->tenant?->id }}</p>
    </x-filament::section>
</x-filament-widgets::widget>

{{-- <x-filament::section>
    <h2 class="text-xl font-bold">
        Selamat Datang
    </h2>

    <p>{{ tenancy()->tenant?->business_name }}</p>
</x-filament::section> --}}
