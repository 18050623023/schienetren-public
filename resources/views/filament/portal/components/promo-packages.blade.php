<style>
    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 2rem;
        padding-top: 1rem;
        padding-right: 1rem;
    }
    @media (min-width: 768px) {
        .pricing-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
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
        position: relative;
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
        gap: 0.5rem;
        margin-top: 1rem;
    }
    .pricing-price-amount {
        font-size: 2rem;
        line-height: 2.5rem;
        font-weight: 700;
        letter-spacing: -0.025em;
        color: #9ca3af;
        text-decoration: line-through;
    }
    .pricing-features-list {
        margin-top: 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5rem;
        color: #4b5563;
        flex-grow: 1;
    }
    .pricing-feature-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .promo-badge {
        position: absolute;
        top: -1rem;
        right: -1rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        font-weight: 800;
        padding: 0.5rem 1.25rem;
        border-radius: 9999px;
        box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4), 0 4px 6px -2px rgba(16, 185, 129, 0.2);
        font-size: 1.125rem;
        transform: rotate(6deg);
        border: 2px solid #ffffff;
        z-index: 10;
    }
    
    /* Dark mode support */
    .dark .pricing-card {
        background-color: #18181b;
        border-color: #27272a;
    }
    .dark .pricing-title { color: #f9fafb; }
    .dark .pricing-desc, .dark .pricing-features-list { color: #9ca3af; }
    .dark .promo-badge { border-color: #18181b; }
</style>

<div class="pricing-grid">
    @forelse($packages as $package)
        <div class="pricing-card">
            <div class="promo-badge">
                Rp{{ number_format($package->price, 0, ',', '.') }}
            </div>
            
            <div class="pricing-header">
                <h3 class="pricing-title">{{ $package->name }}</h3>
                <p class="pricing-desc">Nilai asli saldo yang Anda dapatkan.</p>
                <div class="pricing-price">
                    <span class="pricing-price-amount">Rp{{ number_format($package->received_balance, 0, ',', '.') }}</span>
                </div>
            </div>

            <ul role="list" class="pricing-features-list">
                <li class="pricing-feature-item">
                    <x-filament::icon
                        icon="heroicon-m-check-circle"
                        class="text-primary-600 dark:text-primary-400"
                        style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;"
                    />
                    <span><span style="font-weight: 500;">Saldo Didapat:</span> Rp{{ number_format($package->received_balance, 0, ',', '.') }}</span>
                </li>
                @if($package->received_balance > $package->price)
                <li class="pricing-feature-item">
                    <x-filament::icon
                        icon="heroicon-m-sparkles"
                        class="text-emerald-500 dark:text-emerald-400"
                        style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;"
                    />
                    <span><span style="font-weight: 500;">Hemat:</span> Rp{{ number_format($package->received_balance - $package->price, 0, ',', '.') }}</span>
                </li>
                @endif
            </ul>

            <x-filament::button
                wire:click="buyPackage({{ $package->id }})"
                color="primary"
                style="width: 100%; display: flex; justify-content: center; margin-top: auto;"
            >
                Beli Paket
            </x-filament::button>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; color: #6b7280; padding: 2.5rem 0;">
            Belum ada paket promo yang tersedia.
        </div>
    @endforelse
</div>
