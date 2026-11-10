<style>
    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.5rem;
    }
    @media (min-width: 768px) {
        .pricing-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .pricing-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    .pricing-card {
        display: flex;
        flex-direction: column;
        border-radius: 1.5rem;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        padding: 2rem;
        transition: box-shadow 0.2s ease-in-out;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .pricing-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .pricing-header {
        margin-bottom: 1rem;
    }
    .pricing-title {
        font-size: 1.125rem;
        font-weight: 600;
        line-height: 2rem;
        color: #111827;
    }
    .pricing-desc {
        margin-top: 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5rem;
        color: #4b5563;
    }
    .pricing-price {
        display: flex;
        align-items: baseline;
        gap: 0.25rem;
        margin-top: 1.5rem;
    }
    .pricing-price-amount {
        font-size: 2.25rem;
        line-height: 2.5rem;
        font-weight: 700;
        letter-spacing: -0.025em;
        color: #111827;
    }
    .pricing-price-duration {
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.5rem;
        color: #4b5563;
    }
    .pricing-features-list {
        margin-top: 2rem;
        margin-bottom: 2rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5rem;
        color: #4b5563;
        flex-grow: 1; /* This pushes the button to the bottom */
    }
    .pricing-feature-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    
    /* Dark mode support */
    .dark .pricing-card {
        background-color: #18181b;
        border-color: #27272a;
    }
    .dark .pricing-title, .dark .pricing-price-amount { color: #f9fafb; }
    .dark .pricing-desc, .dark .pricing-price-duration, .dark .pricing-features-list { color: #9ca3af; }
</style>

<div class="pricing-grid">
    @forelse ($plans as $plan)

        <div class="pricing-card" style="position: relative;">
            @if ($currentPlanId && $plan->id === $currentPlanId)
                {{-- Tampilkan badge "Current Plan" di card yang sedang aktif --}}
                <div style="position: absolute; top: 0; left: 50%; transform: translateX(-50%) translateY(-50%);">
                    <span style="background-color: #10b981; color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); white-space: nowrap;">
                        CURRENT PLAN
                    </span>
                </div>
            @endif

            <div class="pricing-header">
                <h3 class="pricing-title">{{ $plan->name }}</h3>
                <p class="pricing-desc">Perfect for scaling your business operations.</p>
                <div class="pricing-price">
                    <span class="pricing-price-amount">Rp{{ number_format($plan->price, 0, ',', '.') }}</span>
                    <span class="pricing-price-duration">/{{ $plan->duration }} {{ $plan->duration_unit }}</span>
                </div>
            </div>

            <ul role="list" class="pricing-features-list">
                @if(is_array($plan->features))
                    {{-- @dd($plan) --}}
                    @foreach($plan->features as $key => $value)
                    <li class="pricing-feature-item">
                        @php
                        $icon = 'heroicon-m-check-circle';
                        $iconColor = 'text-primary-600 dark:text-primary-400';

                        if ($currentPlanId) {
                            $currentPlan = $plans->firstWhere('id', $currentPlanId);
                            if ($currentPlan) {
                                // Cari tahu apakah fitur ini ada di paket saat ini
                                $isCurrentFeature = false;
                                // dd($currentPlan->features);
                                if (isset($currentPlan->features[$key])) {
                                    $currVal = is_array($currentPlan->features[$key]) ? ($currentPlan->features[$key]['value'] ?? null) : $currentPlan->features[$key];
                                    $checkVal = is_array($value) ? ($value['value'] ?? null) : $value;
                                    $isCurrentFeature = $currVal == $checkVal;
                                }
                                
                                // Coba tentukan tingkat upgrade yang sesuai
                                if ($isCurrentFeature) {
                                    // Jika fitur sama, tandai sebagai 'current' (bisa juga pakai warna abu-abu/muted)
                                    $icon = 'heroicon-o-check'; // Icon berbeda dari baru
                                    $iconColor = 'text-gray-400 dark:text-gray-600'; // Warna muted
                                } else {
                                    // Jika fitur tidak ada di paket saat ini, tandai sebagai 'included' (akan di-upgrade)
                                    $icon = 'heroicon-o-sparkles'; 
                                    $iconColor = 'text-emerald-500 dark:text-emerald-400'; // Warna hijau untuk upgrade
                                }
                            }
                        }
                        @endphp
                        <x-filament::icon
                            :icon="$icon"
                            :class="$iconColor"
                            style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;"
                        />
                        
                        @php
                            $featureName = is_array($value) && isset($value['name']) ? $value['name'] : Str::headline($key);
                            $featureValue = is_array($value) && isset($value['value']) ? $value['value'] : $value;
                            $featureType = is_array($value) && isset($value['type']) ? $value['type'] : null;
                        @endphp
                        @if($key === 'addon_key')
                            <span><span style="font-weight: 500;">Addon:</span> {{ is_string($featureValue) ? Str::headline($featureValue) : $featureValue }}</span>
                        @else
                            <span><span style="font-weight: 500;">{{ $featureName }}:</span> 
                            @if(is_bool($featureValue) || $featureType === 'boolean')
                                {{ (bool)$featureValue ? 'Yes' : 'No' }}
                            @else
                                {{ $featureValue }}
                            @endif
                            </span>
                        @endif
                    </li>
                    @endforeach
                @else
                    <li style="color: #9ca3af; font-style: italic;">No specific features listed.</li>
                @endif
            </ul>
            @if (!$currentPlanId || $plan->id !== $currentPlanId)
                <x-filament::button
                    wire:click="{{ isset($plan->is_addon) && $plan->is_addon ? 'buyAddonAction('.$plan->id.')' : 'subscribeToPlan('.$plan->id.')' }}"
                    color="primary"
                    style="width: 100%; display: flex; justify-content: center; margin-top: auto;"
                >
                    Checkout Now
                </x-filament::button>
            @endif
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; color: #6b7280; padding: 2.5rem 0;">
            No plans available at the moment. Please check back later.
        </div>
    @endforelse
</div>
