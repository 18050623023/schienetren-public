<x-filament-panels::page>
    
    {{-- Active Subscription Status Card --}}
    <x-filament::section collapsible>
        <x-slot name="heading">
            {{-- Status Langganan Saat Ini --}}
            <div class="flex items-center gap-2">
                Status Langganan Saat Ini
                
                {{-- Logika untuk menampilkan badge sesuai status --}}
                @if($this->activeSubscription)
                    <x-filament::badge color="success">Aktif</x-filament::badge>
                    
                    {{-- Toggle Auto-Renew --}}
                    <div class="ml-auto flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-medium z-10 relative">Perpanjang Otomatis:</span>
                        <button 
                            wire:click.prevent.stop="toggleAutoRenew"
                            wire:loading.attr="disabled"
                            class="relative z-10 inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 {{ $this->activeSubscription->auto_renew ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}"
                            role="switch" 
                            aria-checked="{{ $this->activeSubscription->auto_renew ? 'true' : 'false' }}"
                        >
                            <span 
                                aria-hidden="true" 
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $this->activeSubscription->auto_renew ? 'translate-x-5' : 'translate-x-0' }}"
                            ></span>
                        </button>
                    </div>
                @else
                    <x-filament::badge color="danger">Tidak Aktif</x-filament::badge>
                @endif
            </div>
        </x-slot>

        @if($this->activeSubscription)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-xl font-bold" style="margin: 0;">{{ $this->activeSubscription->plan->name }}</h3>
                                <x-filament::badge color="success">
                                    Aktif
                                </x-filament::badge>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{-- @dd($this->activeSubscription->plan) --}}
                                Rp {{ number_format($this->activeSubscription->price ?? $this->activeSubscription->plan->price, 0, ',', '.') }}
                                @if($this->activeSubscription->plan->duration_in_days > 0)
                                    @php $duration = \App\Services\TimeConversionService::dayToHuman($this->activeSubscription->plan->duration_in_days); @endphp
                                    / {{ $duration['value'] }} {{ ucfirst($duration['unit']) }}
                                @else
                                    / Selamanya
                                @endif
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <x-filament::button size="sm" wire:click="subscribeToPlan({{ $this->activeSubscription->plan->id }})">
                                Perpanjang Sekarang
                            </x-filament::button>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; padding: 1rem; border-radius: 0.75rem; border: 1px solid var(--gray-200, #e5e7eb); background-color: rgba(0,0,0,0.02);">
                        <div>
                            <span class="block text-gray-500 dark:text-gray-400" style="margin-bottom: 0.25rem; font-size: 0.875rem;">Berlaku Sampai</span>
                            <span class="font-medium" style="font-size: 0.875rem;">{{ $this->activeSubscription->ends_at ? $this->activeSubscription->ends_at->format('d M Y') : 'Selamanya' }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500 dark:text-gray-400" style="margin-bottom: 0.25rem; font-size: 0.875rem;">Perpanjang Otomatis</span>
                            <span class="font-medium {{ $this->activeSubscription->auto_renew ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}" style="display: flex; align-items: center; gap: 0.25rem; font-size: 0.875rem;">
                                <x-filament::icon icon="{{ $this->activeSubscription->auto_renew ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle' }}" class="w-4 h-4" />
                                {{ $this->activeSubscription->auto_renew ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                    
                    @if($this->scheduledSubscription)
                    <div style="margin-top: 1rem; padding: 1rem; border-radius: 0.75rem; border: 1px solid var(--warning-200, #fef3c7); " class="dark:border-warning-700 dark:bg-warning-900/20">
                        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                            <x-filament::icon icon="heroicon-m-clock" class="w-5 h-5 text-warning-600 dark:text-warning-400 mt-0.5 flex-shrink-0" />
                            <div>
                                <h4 class="font-medium text-warning-800 dark:text-warning-300" style="margin-bottom: 0.25rem;">Paket Mendatang: {{ $this->scheduledSubscription->plan->name }}</h4>
                                <p class="text-sm text-warning-700 dark:text-warning-400">
                                    Paket ini sudah dijadwalkan dan akan aktif secara otomatis setelah paket Anda saat ini habis (mulai {{ $this->scheduledSubscription->starts_at->format('d M Y') }} hingga {{ $this->scheduledSubscription->ends_at->format('d M Y') }}).
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Fitur yang didapatkan:</h4>
                    @if($this->activeSubscription->plan->planFeatures && $this->activeSubscription->plan->planFeatures->count() > 0)
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 p-0 m-0 list-none">
                            @foreach($this->activeSubscription->plan->planFeatures as $planFeature)
                                @php
                                    $feature = $planFeature->feature;
                                    if (!$feature) continue;
                                    $key = $feature->name;
                                    $value = $planFeature->value;
                                @endphp
                                <li class="text-sm text-gray-600 dark:text-gray-300" style="display: flex; align-items: flex-start; gap: 0.5rem;">
                                    <x-filament::icon icon="heroicon-m-check-circle" class="w-5 h-5 text-primary-500" style="flex-shrink: 0;" />
                                    <span>
                                        <strong>{{ $key }}:</strong> 
                                        @if($feature->type === 'boolean')
                                            {{ in_array($value, [true, 'true', '1', 1], true) ? 'Aktif' : 'Nonaktif' }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">Tidak ada detail fitur yang tersedia.</p>
                    @endif
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center p-6 text-center space-y-4">
                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-full">
                    <x-filament::icon
                        icon="heroicon-o-information-circle"
                        class="w-8 h-8 text-gray-500 dark:text-gray-400"
                    />
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Anda belum memiliki paket aktif</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Silakan mulai berlangganan dengan memilih paket yang tersedia melalui tombol di atas.</p>
                </div>
            </div>
        @endif
    </x-filament::section>

{{-- Wallet Top Bar --}}
    <div class="mb-3 flex items-center justify-between rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex items-center gap-x-3">
            <x-filament::icon icon="heroicon-o-wallet" class="h-5 w-5 text-primary-500" />
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Saldo Dompet:</span>
            <span class="text-base font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($balance, 0, ',', '.') }}</span>
        </div>
        <div class="flex items-center gap-3">
            {{-- {{ $this->viewPromosAction }} --}}
            {{ $this->manualTopupAction }}
        </div>
    </div>


    {{-- Payment History Table --}}
    <div class="mt-1">
        <h2 class="text-lg font-semibold mb-4">Riwayat Pembayaran</h2>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
