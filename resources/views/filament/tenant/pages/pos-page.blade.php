<x-filament-panels::page :full-height="true">
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .no-scrollbar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
    </style>

    <div 
        x-data="{
            isFullscreen: false,
            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    if (document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.error('Fullscreen request failed:', err);
                        });
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen().catch(err => {
                            console.error('Exit fullscreen failed:', err);
                        });
                    }
                }
            },
            init() {
                let syncState = () => {
                    this.isFullscreen = !!document.fullscreenElement;
                };
                document.addEventListener('fullscreenchange', syncState);
                document.addEventListener('webkitfullscreenchange', syncState);
            }
        }"
        :class="isFullscreen 
            ? 'fixed inset-0 z-50 bg-gray-100 dark:bg-gray-950 p-3 h-screen w-screen overflow-hidden flex flex-col' 
            : 'h-[calc(100vh-8.5rem)] flex flex-col overflow-hidden'"
    >
        {{-- Flex 2 Kolom: Selalu Side-by-Side di Desktop/Tablet --}}
        <div class="flex-1 flex flex-row gap-3.5 overflow-hidden min-h-0">

            {{-- ==================== KOLOM KIRI: KATALOG PRODUK ==================== --}}
            <div class="flex-1 min-w-0 flex flex-col overflow-hidden min-h-0 bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 rounded-xl shadow-xs px-3.5 py-2.5">
                
                {{-- Header Katalog & Cabang & Fullscreen Toggle --}}
                <div class="px-3.5 py-2.5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-primary-500/10 text-primary-500">
                            <x-filament::icon icon="heroicon-m-shopping-bag" class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="font-bold text-sm text-gray-900 dark:text-white leading-tight">Point of Sale (POS)</h2>
                            <p class="text-[11px] text-gray-500 hidden sm:block">Pilih barang atau scan barcode untuk menambah transaksi</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Selektor Lokasi Cabang --}}
                        <div class="w-40 sm:w-48">
                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="branchId">
                                    @foreach(\App\Models\Tenant\Branch::all() as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        {{-- Tombol Toggle Fullscreen Kiosk --}}
                        <x-filament::button
                            type="button"
                            color="gray"
                            size="sm"
                            @click="toggleFullscreen()"
                            title="Beralih ke Layar Penuh Kasir"
                        >
                            <span class="flex items-center gap-1.5">
                                <span x-show="!isFullscreen" class="flex items-center">
                                    <x-filament::icon icon="heroicon-m-arrows-pointing-out" class="w-4 h-4" />
                                </span>
                                <span x-show="isFullscreen" x-cloak class="flex items-center">
                                    <x-filament::icon icon="heroicon-m-arrows-pointing-in" class="w-4 h-4" />
                                </span>
                                <span class="hidden md:inline" x-text="isFullscreen ? 'Keluar Layar Penuh' : 'Layar Penuh'"></span>
                            </span>
                        </x-filament::button>
                    </div>
                </div>

                {{-- Bar Pencarian & Filter Kategori --}}
                <div class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 shrink-0">
                    {{-- Input Pencarian --}}
                    <div class="px-3.5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                                    <x-filament::input
                                        type="text"
                                        wire:model.live.debounce.300ms="search"
                                        placeholder="Ketik nama sparepart, varian, SKU, atau scan barcode..."
                                        autofocus
                                    />
                                </x-filament::input.wrapper>
                            </div>
                            @if(!empty($search))
                                <x-filament::button
                                    wire:click="$set('search', '')"
                                    color="gray"
                                    size="sm"
                                >
                                    Reset
                                </x-filament::button>
                            @endif
                        </div>
                    </div>

                    {{-- Tombol Filter Kategori (Clean Scroll, No Ugly Scrollbar) --}}
                    <div class="px-3.5 pt-2 pb-2.5 mb-3">
                        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                            <button
                                type="button"
                                wire:click="selectCategory(null)"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ is_null($selectedCategoryId) ? 'bg-primary-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-750' }}"
                            >
                                Semua Kategori
                            </button>
                            @foreach(\App\Models\Tenant\Category::forProduct()->get() as $cat)
                                <button
                                    type="button"
                                    wire:click="selectCategory({{ $cat->id }})"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $selectedCategoryId === $cat->id ? 'bg-primary-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-750' }}"
                                >
                                    {{ $cat->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Grid Produk Scrollable --}}
                <div class="flex-1 overflow-y-auto min-h-0 p-3.5">
                    @php $products = $this->availableProducts; @endphp
                    @if($products->isEmpty())
                        <div class="flex flex-col items-center justify-center h-48 text-center text-gray-400">
                            <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-10 h-10 mb-2 opacity-40" />
                            <p class="font-medium text-xs">Tidak ada produk ditemukan</p>
                            <p class="text-[11px] text-gray-500">Sesuaikan kata kunci pencarian atau ganti cabang.</p>
                        </div>
                    @else
                        <div class="grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));">
                            @foreach($products as $variant)
                                @php
                                    $stock = $variant->current_branch_stock;
                                    $isOutOfStock = $stock <= 0;
                                @endphp
                                <div 
                                    wire:key="prod-{{ $variant->id }}"
                                    @if(!$isOutOfStock) wire:click="addToCart({{ $variant->id }})" @endif
                                    class="relative flex flex-col justify-between p-3 rounded-xl border transition select-none h-full {{ $isOutOfStock ? 'opacity-50 cursor-not-allowed bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800' : 'cursor-pointer bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-400 hover:shadow-xs' }}"
                                >
                                    <div>
                                        <div class="flex items-start justify-between gap-1 mb-1.5">
                                            <x-filament::badge :color="$stock > 5 ? 'success' : ($stock > 0 ? 'warning' : 'danger')" size="sm">
                                                {{ $stock > 0 ? "Stok: {$stock}" : 'Habis' }}
                                            </x-filament::badge>
                                            @if($variant->product?->has_sn)
                                                <x-filament::badge color="info" size="sm" title="Item memerlukan nomor seri/IMEI">
                                                    SN/IMEI
                                                </x-filament::badge>
                                            @endif
                                        </div>

                                        <h4 class="font-bold text-xs text-gray-900 dark:text-white line-clamp-2 leading-snug">
                                            {{ $variant->product?->name }}
                                            @if($variant->name)
                                                <span class="text-gray-500 dark:text-gray-400 font-normal">({{ $variant->name }})</span>
                                            @endif
                                        </h4>

                                        <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 font-mono">
                                            {{ $variant->sku ?: 'Tanpa SKU' }}
                                        </div>
                                    </div>

                                    <div class="mt-3 pt-2 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                        <span class="font-black text-xs text-primary-600 dark:text-primary-400">
                                            Rp {{ number_format($variant->sale_price, 0, ',', '.') }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 font-medium">
                                            + Tambah
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- ==================== KOLOM KANAN: KERANJANG KASIR ==================== --}}
            <div class="w-[360px] lg:w-[400px] shrink-0 flex flex-col overflow-hidden min-h-0 bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 rounded-xl shadow-xs">
                
                {{-- Header Keranjang & Pelanggan --}}
                <div class="px-3.5 py-2.5 border-b border-gray-100 dark:border-gray-800 shrink-0">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="p-1 rounded-md bg-primary-500/10 text-primary-500">
                                <x-filament::icon icon="heroicon-m-shopping-cart" class="w-4 h-4" />
                            </div>
                            <span class="font-bold text-sm text-gray-900 dark:text-white">Pesanan Kasir</span>
                            @if(!empty($cart))
                                <x-filament::badge color="primary" size="sm">
                                    {{ count($cart) }} Item
                                </x-filament::badge>
                            @endif
                        </div>
                        @if(!empty($cart))
                            <button wire:click="clearCart" class="text-xs text-danger-600 hover:text-danger-700 font-medium cursor-pointer">
                                Kosongkan
                            </button>
                        @endif
                    </div>

                    {{-- Pilih Pelanggan --}}
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <x-filament::input.wrapper prefix-icon="heroicon-m-user">
                                <x-filament::input.select wire:model.live="selectedClientId">
                                    <option value="">👤 Pelanggan Umum (Walk-in)</option>
                                    @foreach(\App\Models\Tenant\Client::orderBy('name')->get() as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone ?: 'No telp' }})</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                        <x-filament::button
                            type="button"
                            wire:click="openNewClientModal"
                            color="gray"
                            size="sm"
                            icon="heroicon-m-user-plus"
                            title="Tambah Pelanggan Baru"
                        />
                    </div>
                </div>

                {{-- Daftar Item Keranjang (Scrollable) --}}
                <div class="flex-1 overflow-y-auto min-h-0 p-3 divide-y divide-gray-100 dark:divide-gray-800">
                    @if(empty($cart))
                        <div class="flex flex-col items-center justify-center h-full text-center text-gray-400 py-6">
                            <x-filament::icon icon="heroicon-o-shopping-bag" class="w-10 h-10 mb-2 opacity-40" />
                            <p class="font-medium text-xs">Keranjang masih kosong</p>
                            <p class="text-[11px] text-gray-500">Pilih barang di sebelah kiri untuk menambahkan transaksi.</p>
                        </div>
                    @else
                        @foreach($cart as $idx => $item)
                            <div wire:key="cart-item-{{ $idx }}" class="py-2.5 first:pt-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1">
                                        <h5 class="text-xs font-bold text-gray-900 dark:text-white leading-snug">
                                            {{ $item['name'] }}
                                        </h5>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                            @ Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <button wire:click="removeFromCart({{ $idx }})" class="text-gray-400 hover:text-danger-500 p-0.5 cursor-pointer">
                                        <x-filament::icon icon="heroicon-m-trash" class="w-4 h-4" />
                                    </button>
                                </div>

                                {{-- Pilihan Serial Number (jika item ber-serial) --}}
                                @if($item['has_serial'])
                                    <div class="mt-1.5 p-2 bg-info-50 dark:bg-info-950/30 rounded-lg border border-info-200 dark:border-info-900/50">
                                        <div class="text-[10px] font-bold text-info-700 dark:text-info-300 mb-1 flex items-center gap-1">
                                            <x-filament::icon icon="heroicon-m-qr-code" class="w-3.5 h-3.5" />
                                            <span>Pilih Serial / IMEI:</span>
                                        </div>
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select 
                                                wire:change="$set('cart.{{ $idx }}.selected_serials', [$event.target.value])" 
                                            >
                                                <option value="">-- Pilih Nomor Seri --</option>
                                                @foreach($item['available_serials'] as $snId => $snVal)
                                                    @php
                                                        $isTakenInOtherRow = false;
                                                        foreach ($cart as $otherIdx => $otherItem) {
                                                            if ($otherIdx !== $idx && in_array($snId, $otherItem['selected_serials'] ?? [])) {
                                                                $isTakenInOtherRow = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    @if(!$isTakenInOtherRow || in_array($snId, $item['selected_serials'] ?? []))
                                                        <option value="{{ $snId }}" {{ in_array($snId, $item['selected_serials'] ?? []) ? 'selected' : '' }}>{{ $snVal }}</option>
                                                    @endif
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    </div>
                                @endif

                                {{-- Kuantitas & Subtotal Item --}}
                                <div class="mt-2 flex items-center justify-between">
                                    @if(!$item['has_serial'])
                                        <div class="flex items-center gap-1">
                                            <button 
                                                type="button"
                                                wire:click="updateQty({{ $idx }}, {{ $item['qty'] - 1 }})" 
                                                class="w-6 h-6 rounded bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 flex items-center justify-center font-bold text-xs cursor-pointer"
                                            >-</button>
                                            <span class="w-8 text-center font-bold text-xs">{{ $item['qty'] }}</span>
                                            <button 
                                                type="button"
                                                wire:click="updateQty({{ $idx }}, {{ $item['qty'] + 1 }})" 
                                                class="w-6 h-6 rounded bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 flex items-center justify-center font-bold text-xs cursor-pointer"
                                            >+</button>
                                        </div>
                                    @else
                                        {{-- Sembunyikan field kuantitas jika produk ber-SN (1 baris mewakili 1 unit unik) --}}
                                        <div class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                                            <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 font-bold text-[10px]">1 Unit</span>
                                        </div>
                                    @endif
                                    <div class="font-extrabold text-xs text-gray-900 dark:text-white">
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Footer Keranjang & Kalkulasi (Sticky di Bawah) --}}
                <div class="p-3 border-t border-gray-100 dark:border-gray-800 space-y-1.5 text-xs bg-gray-50/70 dark:bg-gray-900/70 shrink-0">
                    <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Subtotal</span>
                        <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                    </div>

                    {{-- Diskon Global --}}
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">Diskon</span>
                        <div class="w-44 shrink-0 py-2">
                            <x-filament::input.wrapper>
                                <x-slot name="prefix">
                                    <button 
                                        type="button" 
                                        wire:click="$set('discountType', '{{ $discountType === 'fixed' ? 'percentage' : 'fixed' }}')"
                                        class="flex items-center gap-1 font-bold text-xs text-primary-600 dark:text-primary-400 hover:text-primary-500 cursor-pointer select-none -ms-1 py-0.5 px-1 rounded transition focus:outline-hidden"
                                        title="Klik untuk beralih antara Rp dan %"
                                    >
                                        <span>{{ $discountType === 'fixed' ? 'Rp' : '%' }}</span>
                                        <x-filament::icon icon="heroicon-m-arrows-up-down" class="w-3 h-3 opacity-60" />
                                    </button>
                                </x-slot>
                                <x-filament::input 
                                    type="number" 
                                    wire:model.live="discountValue" 
                                    min="0"
                                    class="text-right text-xs"
                                    placeholder="0"
                                />
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                    @if($this->discountAmount > 0)
                        <div class="flex justify-between text-danger-600 text-[11px] font-medium pl-1">
                            <span>Potongan Diskon:</span>
                            <span>- Rp {{ number_format($this->discountAmount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    {{-- Pajak (Opsional) --}}
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">Pajak</span>
                        <div class="w-44 shrink-0">
                            <x-filament::input.wrapper prefix="Rp">
                                <x-filament::input 
                                    type="number" 
                                    wire:model.live="taxAmount" 
                                    min="0"
                                    class="text-right text-xs"
                                    placeholder="0"
                                />
                            </x-filament::input.wrapper>
                        </div>
                    </div>

                    {{-- Total Tagihan Akhir --}}
                    <div class="pt-2 border-t border-dashed border-gray-200 dark:border-gray-700 flex justify-between items-baseline">
                        <span class="font-bold text-xs text-gray-900 dark:text-white uppercase tracking-wider">Total Akhir</span>
                        <span class="font-black text-xl text-primary-600 dark:text-primary-400">
                            Rp {{ number_format($this->grandTotal, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Tombol Bayar Sekarang --}}
                    <div class="pt-1">
                        <x-filament::button
                            wire:click="openPaymentModal"
                            color="primary"
                            size="lg"
                            class="w-full justify-center text-sm font-bold shadow-md cursor-pointer"
                            :disabled="empty($cart)"
                            icon="heroicon-m-credit-card"
                        >
                            Bayar Sekarang (Rp {{ number_format($this->grandTotal, 0, ',', '.') }})
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL 1: PEMBAYARAN KASIR ==================== --}}
    <x-filament::modal id="pos-payment-modal" width="lg">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-primary-500/10 text-primary-500">
                    <x-filament::icon icon="heroicon-m-banknotes" class="w-5 h-5" />
                </div>
                <span class="font-bold text-base text-gray-900 dark:text-white">Pembayaran Kasir POS</span>
            </div>
        </x-slot>

        <div class="space-y-4 py-2">
            {{-- Ringkasan Tagihan --}}
            <div class="p-3.5 bg-primary-50 dark:bg-primary-950/40 rounded-xl border border-primary-200 dark:border-primary-900 flex justify-between items-center">
                <span class="text-xs font-semibold text-primary-900 dark:text-primary-200 uppercase tracking-wider">Total Tagihan:</span>
                <span class="font-black text-2xl text-primary-600 dark:text-primary-400">
                    Rp {{ number_format($this->grandTotal, 0, ',', '.') }}
                </span>
            </div>

            {{-- Pilihan Metode Pembayaran --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Metode Pembayaran:</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(\App\Models\Tenant\PaymentMethod::where('is_active', true)->get() as $pm)
                        <button 
                            type="button" 
                            wire:click="$set('paymentMethodId', {{ $pm->id }})" 
                            class="p-2.5 rounded-xl border text-xs font-bold text-center transition cursor-pointer {{ $paymentMethodId === $pm->id ? 'bg-primary-600 text-white border-primary-600 shadow-sm' : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                        >
                            {{ $pm->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Input Uang Diterima & Tombol Pintas Pecahan --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Uang Diterima dari Pelanggan:</label>
                <x-filament::input.wrapper prefix="Rp">
                    <x-filament::input 
                        type="number" 
                        wire:model.live="paidAmount" 
                        class="text-right text-lg font-black"
                        min="0"
                    />
                </x-filament::input.wrapper>

                {{-- Preset Uang Pas & Pecahan --}}
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <button type="button" wire:click="setPaidPreset({{ $this->grandTotal }})" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 cursor-pointer">
                        Uang Pas
                    </button>
                    <button type="button" wire:click="setPaidPreset(50000)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 cursor-pointer">
                        50.000
                    </button>
                    <button type="button" wire:click="setPaidPreset(100000)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 cursor-pointer">
                        100.000
                    </button>
                    <button type="button" wire:click="setPaidPreset(200000)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 cursor-pointer">
                        200.000
                    </button>
                    <button type="button" wire:click="addPaidPreset(10000)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 cursor-pointer">
                        +10.000
                    </button>
                    <button type="button" wire:click="addPaidPreset(50000)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 cursor-pointer">
                        +50.000
                    </button>
                </div>
            </div>

            {{-- Uang Kembalian --}}
            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-xl flex justify-between items-center border border-gray-100 dark:border-gray-800">
                <span class="text-xs font-bold text-gray-600 dark:text-gray-300">Kembalian:</span>
                <span class="font-extrabold text-lg {{ $this->changeAmount > 0 ? 'text-success-600 dark:text-success-400' : 'text-gray-500' }}">
                    Rp {{ number_format($this->changeAmount, 0, ',', '.') }}
                </span>
            </div>

            {{-- Catatan --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Catatan Tambahan (Opsional):</label>
                <x-filament::input.wrapper>
                    <x-filament::input 
                        type="text" 
                        wire:model="notes" 
                        placeholder="Keterangan transaksi bila ada..."
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex gap-2 w-full justify-end">
                <x-filament::button
                    type="button"
                    color="gray"
                    wire:click="closePaymentModal"
                >
                    Batal
                </x-filament::button>
                <x-filament::button
                    type="button"
                    color="success"
                    wire:click="processCheckout"
                    icon="heroicon-m-check-circle"
                >
                    Selesaikan & Simpan
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- ==================== MODAL 2: SUKSES & CETAK STRUK ==================== --}}
    <x-filament::modal id="pos-success-modal" width="md" :close-by-clicking-away="false">
        <x-slot name="heading">
            <div class="text-center w-full">
                <div class="w-12 h-12 bg-success-500/10 text-success-500 rounded-full flex items-center justify-center mx-auto mb-2">
                    <x-filament::icon icon="heroicon-o-check" class="w-6 h-6" />
                </div>
                <h3 class="font-extrabold text-lg text-gray-900 dark:text-white">Transaksi Berhasil!</h3>
                <p class="text-xs text-gray-500 font-mono mt-0.5">No. Faktur: <span class="font-bold text-gray-800 dark:text-gray-200">{{ $lastInvoiceNumber }}</span></p>
            </div>
        </x-slot>

        <div class="p-3.5 bg-gray-50 dark:bg-gray-800 rounded-xl space-y-1.5 text-xs border border-gray-100 dark:border-gray-800">
            <div class="flex justify-between">
                <span class="text-gray-500">Total Belanja:</span>
                <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($lastGrandTotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Uang Kembalian:</span>
                <span class="font-bold text-success-600 dark:text-success-400">Rp {{ number_format($lastChangeAmount, 0, ',', '.') }}</span>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex flex-col gap-2 w-full">
                @if($lastSaleId)
                    <x-filament::button
                        tag="a"
                        href="{{ route('tenant.pos.sales.print', $lastSaleId) }}"
                        target="_blank"
                        color="primary"
                        icon="heroicon-m-printer"
                        class="w-full justify-center"
                    >
                        Cetak Struk Belanja
                    </x-filament::button>
                @endif
                <x-filament::button
                    type="button"
                    color="gray"
                    wire:click="$dispatch('close-modal', { id: 'pos-success-modal' })"
                    class="w-full justify-center"
                >
                    Transaksi Baru (Selesai)
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- ==================== MODAL 3: TAMBAH PELANGGAN CEPAT ==================== --}}
    <x-filament::modal id="pos-client-modal" width="md">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <div class="p-1 rounded-md bg-primary-500/10 text-primary-500">
                    <x-filament::icon icon="heroicon-m-user-plus" class="w-4 h-4" />
                </div>
                <span class="font-bold text-sm text-gray-900 dark:text-white">Tambah Pelanggan Baru</span>
            </div>
        </x-slot>

        <div class="space-y-3 py-2 text-xs">
            <div>
                <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Nama Lengkap:</label>
                <x-filament::input.wrapper>
                    <x-filament::input 
                        type="text" 
                        wire:model="newClientName" 
                        placeholder="Contoh: Budi Santoso"
                    />
                </x-filament::input.wrapper>
            </div>
            <div>
                <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">No. WhatsApp / Telepon:</label>
                <x-filament::input.wrapper prefix-icon="heroicon-m-phone">
                    <x-filament::input 
                        type="text" 
                        wire:model="newClientPhone" 
                        placeholder="Contoh: 08123456789"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex gap-2 w-full justify-end">
                <x-filament::button
                    type="button"
                    color="gray"
                    wire:click="closeNewClientModal"
                >
                    Batal
                </x-filament::button>
                <x-filament::button
                    type="button"
                    color="primary"
                    wire:click="quickCreateClient"
                >
                    Simpan Pelanggan
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

</x-filament-panels::page>
