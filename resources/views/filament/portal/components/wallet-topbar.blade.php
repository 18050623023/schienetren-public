<div class="flex items-center gap-4 mx-4 border-l border-gray-200 dark:border-gray-700 pl-4">
    <div class="text-sm font-medium text-gray-700 dark:text-gray-200 hidden sm:block">
        Saldo: <span class="text-primary-600 font-bold">Rp {{ number_format(Auth::user()->wallet->balance ?? 0, 0, ',', '.') }}</span>
    </div>
    <x-filament::button href="/portal/billing" tag="a" size="sm" color="primary" outlined>
        Top Up
    </x-filament::button>
</div>
