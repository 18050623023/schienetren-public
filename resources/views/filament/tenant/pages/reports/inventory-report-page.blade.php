<x-filament-panels::page>
    {{-- Form Filter Terpadu --}}
    <form wire:submit.prevent class="space-y-4">
        {{ $this->form }}
    </form>

    {{-- Navigasi Tab & Tombol Aksi (Export / Print) --}}
    <div class="flex flex-col lg:flex-row items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-800 pb-3">
        {{-- Tab Switcher --}}
        <div class="flex flex-wrap items-center gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
            <button
                type="button"
                wire:click="setTab('valuation')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'valuation' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-currency-dollar" class="w-4 h-4" />
                    <span>Valuasi Stok</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('movements')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'movements' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-arrows-right-left" class="w-4 h-4" />
                    <span>Kartu Stok & Mutasi</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('opnames')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'opnames' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-clipboard-document-check" class="w-4 h-4" />
                    <span>Selisih Opname</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('transfers')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'transfers' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-truck" class="w-4 h-4" />
                    <span>Transfer Cabang</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('purchases')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'purchases' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-shopping-cart" class="w-4 h-4" />
                    <span>Pengadaan PO</span>
                </div>
            </button>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 w-full lg:w-auto justify-end">
            <x-filament::button
                wire:click="exportCsv"
                color="gray"
                icon="heroicon-o-arrow-down-tray"
                size="sm"
            >
                Unduh CSV
            </x-filament::button>

            <x-filament::button
                wire:click="printReport"
                color="primary"
                icon="heroicon-o-printer"
                size="sm"
            >
                Cetak / Simpan PDF
            </x-filament::button>
        </div>
    </div>

    @php
        $reportData = $this->reportData;
        $kpi = $reportData['kpi'] ?? [];
    @endphp

    {{-- KONTEN TAB 1: VALUASI STOK --}}
    @if ($activeTab === 'valuation')
        <div class="space-y-6">
            {{-- KPI Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Total Nilai Aset Modal</span>
                        <x-filament::icon icon="heroicon-o-banknotes" class="w-5 h-5 text-primary-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-primary-600 dark:text-primary-400">
                        Rp {{ number_format($kpi['total_cost_value'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ number_format($kpi['total_units'] ?? 0) }} total unit fisik</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Potensi Omset Jual</span>
                        <x-filament::icon icon="heroicon-o-arrow-trending-up" class="w-5 h-5 text-success-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">
                        Rp {{ number_format($kpi['total_sale_value'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Estimasi nilai penjualan</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Estimasi Laba Kotor</span>
                        <x-filament::icon icon="heroicon-o-chart-bar" class="w-5 h-5 text-info-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-info-600 dark:text-info-400">
                        Rp {{ number_format($kpi['potential_margin'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Potensi keuntungan gudang</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Peringatan Stok Menipis</span>
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-5 h-5 text-warning-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-warning-600 dark:text-warning-400">
                        {{ $kpi['low_stock_count'] ?? 0 }} SKU
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Stok kosong: {{ $kpi['out_of_stock_count'] ?? 0 }} SKU</p>
                </div>
            </div>

            {{-- Tabel Rincian Valuasi Stok --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Daftar Persediaan & Nilai Aset Gudang</h3>
                    <span class="text-xs text-gray-500">Total: {{ count($reportData['rows'] ?? []) }} Barang</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-medium">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">SKU</th>
                                <th class="px-4 py-3">Produk & Varian</th>
                                <th class="px-4 py-3">Kategori / Brand</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3 text-center">Sisa Stok</th>
                                <th class="px-4 py-3 text-right">Harga Modal</th>
                                <th class="px-4 py-3 text-right">Harga Jual</th>
                                <th class="px-4 py-3 text-right">Total Aset (HPP)</th>
                                <th class="px-4 py-3 text-right">Est. Margin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($reportData['rows'] ?? [] as $index => $r)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $r['sku'] }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $r['product_name'] }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $r['brand'] }} / {{ $r['category'] }}</td>
                                    <td class="px-4 py-3">{{ $r['branch'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $r['stock'] <= 0 ? 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400' : ($r['stock'] <= 5 ? 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400' : 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400') }}">
                                            {{ $r['stock'] }} {{ $r['unit'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">Rp {{ number_format($r['purchase_price'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">Rp {{ number_format($r['sale_price'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">Rp {{ number_format($r['total_cost'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-success-600 font-medium">+Rp {{ number_format($r['potential_margin'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data stok pada cabang atau filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 2: KARTU STOK & MUTASI --}}
    @if ($activeTab === 'movements')
        <div class="space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Total Transaksi Mutasi</span>
                        <x-filament::icon icon="heroicon-o-clipboard-document-list" class="w-5 h-5 text-primary-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $kpi['total_movements'] ?? 0 }} Kali
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Dalam periode filter</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Barang Masuk (IN)</span>
                        <x-filament::icon icon="heroicon-o-arrow-down-left" class="w-5 h-5 text-success-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">
                        +{{ number_format($kpi['total_qty_in'] ?? 0) }} Unit
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Pengadaan PO / Penerimaan</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Barang Keluar (OUT)</span>
                        <x-filament::icon icon="heroicon-o-arrow-up-right" class="w-5 h-5 text-danger-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-danger-600 dark:text-danger-400">
                        -{{ number_format($kpi['total_qty_out'] ?? 0) }} Unit
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Terpakai servis / penjualan</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Arus Bersih (Net Flow)</span>
                        <x-filament::icon icon="heroicon-o-scale" class="w-5 h-5 text-info-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold {{ ($kpi['net_flow'] ?? 0) >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        {{ ($kpi['net_flow'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($kpi['net_flow'] ?? 0) }} Unit
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Selisih arus barang</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Riwayat Mutasi & Kartu Stok</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-medium">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Waktu Transaksi</th>
                                <th class="px-4 py-3">Produk & Varian</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3 text-center">Arah</th>
                                <th class="px-4 py-3 text-center">Jumlah (Qty)</th>
                                <th class="px-4 py-3">Sumber Mutasi</th>
                                <th class="px-4 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($reportData['movements'] ?? [] as $index => $m)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $m->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                        {{ $m->product?->name ?? 'Produk' }} {{ $m->productVariant?->name ?? '' }}
                                    </td>
                                    <td class="px-4 py-3">{{ $m->branch?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $m->direction === 'in' ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400' : 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400' }}">
                                            {{ strtoupper($m->direction) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold {{ $m->direction === 'in' ? 'text-success-600' : 'text-danger-600' }}">
                                        {{ $m->direction === 'in' ? '+' : '-' }}{{ $m->quantity }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 font-mono">{{ class_basename($m->source_type ?? 'Manual') }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate">{{ $m->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada riwayat mutasi stok pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 3: SELISIH STOCK OPNAME --}}
    @if ($activeTab === 'opnames')
        <div class="space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Total Audit Selesai</span>
                        <x-filament::icon icon="heroicon-o-document-check" class="w-5 h-5 text-primary-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $kpi['total_audits'] ?? 0 }} Sesi
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ $kpi['total_items_audited'] ?? 0 }} item diperiksa</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Barang Hilang / Kurang</span>
                        <x-filament::icon icon="heroicon-o-minus-circle" class="w-5 h-5 text-danger-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-danger-600 dark:text-danger-400">
                        {{ number_format($kpi['total_deficit_qty'] ?? 0) }} Unit
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Selisih fisik minus</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Barang Berlebih / Surplus</span>
                        <x-filament::icon icon="heroicon-o-plus-circle" class="w-5 h-5 text-success-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">
                        +{{ number_format($kpi['total_surplus_qty'] ?? 0) }} Unit
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Fisik lebih dari sistem</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Estimasi Kerugian Selisih</span>
                        <x-filament::icon icon="heroicon-o-receipt-percent" class="w-5 h-5 text-warning-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-warning-600 dark:text-warning-400">
                        Rp {{ number_format($kpi['total_loss_value'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Nominal nilai barang hilang</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Rincian Audit Selisih Stock Opname</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-medium">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">No. Referensi</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3">Produk & Varian</th>
                                <th class="px-4 py-3 text-center">Stok Sistem</th>
                                <th class="px-4 py-3 text-center">Fisik</th>
                                <th class="px-4 py-3 text-center">Selisih</th>
                                <th class="px-4 py-3 text-right">Nilai Selisih</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @php $hasOpnameRows = false; @endphp
                            @foreach ($reportData['opnames'] ?? [] as $op)
                                @foreach ($op->items as $item)
                                    @php
                                        $hasOpnameRows = true;
                                        $diff = (int) $item->difference;
                                        $cost = (float) ($item->productVariant?->purchase_price ?? 0);
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                        <td class="px-4 py-3 text-xs text-gray-500">{{ $op->date?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ $op->reference }}</td>
                                        <td class="px-4 py-3">{{ $op->branch?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ $item->product?->name ?? '-' }} {{ $item->productVariant?->name ?? '' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">{{ $item->system_qty }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">{{ $item->actual_qty }}</td>
                                        <td class="px-4 py-3 text-center font-bold {{ $diff < 0 ? 'text-danger-600' : ($diff > 0 ? 'text-success-600' : 'text-gray-400') }}">
                                            {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium {{ $diff < 0 ? 'text-danger-600' : ($diff > 0 ? 'text-success-600' : 'text-gray-400') }}">
                                            Rp {{ number_format(abs($diff * $cost), 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $op->status === 'posted' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400' }}">
                                                {{ strtoupper($op->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach

                            @if (!$hasOpnameRows)
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data audit selisih stock opname pada periode ini.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 4: TRANSFER ANTAR CABANG --}}
    @if ($activeTab === 'transfers')
        <div class="space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Total Pengiriman</span>
                        <x-filament::icon icon="heroicon-o-truck" class="w-5 h-5 text-primary-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $kpi['total_transfers'] ?? 0 }} Pengiriman
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ $kpi['total_items_count'] ?? 0 }} unit dipindahkan</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Dalam Pengiriman</span>
                        <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 text-warning-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-warning-600 dark:text-warning-400">
                        {{ $kpi['in_transit_count'] ?? 0 }} Mutasi
                    </div>
                    <p class="mt-1 text-xs text-gray-500">In Transit antar cabang</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Berhasil Diterima</span>
                        <x-filament::icon icon="heroicon-o-check-badge" class="w-5 h-5 text-success-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">
                        {{ $kpi['received_count'] ?? 0 }} Mutasi
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Diterima di cabang tujuan</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Mutasi Dibatalkan</span>
                        <x-filament::icon icon="heroicon-o-x-circle" class="w-5 h-5 text-danger-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-danger-600 dark:text-danger-400">
                        {{ $kpi['canceled_count'] ?? 0 }} Mutasi
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Canceled</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Daftar Mutasi Antar Cabang</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-medium">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Kode Mutasi</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Pengirim</th>
                                <th class="px-4 py-3">Tujuan</th>
                                <th class="px-4 py-3 text-center">Macam Barang</th>
                                <th class="px-4 py-3 text-center">Total Unit</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($reportData['transfers'] ?? [] as $index => $t)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ $t->code }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($t->date)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $t->fromBranch?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-medium text-primary-600 dark:text-primary-400">{{ $t->toBranch?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">{{ $t->items->count() }} Jenis</td>
                                    <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">{{ $t->items->sum('quantity') }} Qty</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $t->status === 'received' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : ($t->status === 'in_transit' ? 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400') }}">
                                            {{ strtoupper($t->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada riwayat transfer barang pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 5: PENGADAAN & PO --}}
    @if ($activeTab === 'purchases')
        <div class="space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Total Belanja PO</span>
                        <x-filament::icon icon="heroicon-o-shopping-bag" class="w-5 h-5 text-primary-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-primary-600 dark:text-primary-400">
                        Rp {{ number_format($kpi['total_spend'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ $kpi['total_pos'] ?? 0 }} Transaksi PO</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Sudah Dibayarkan</span>
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-success-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">
                        Rp {{ number_format($kpi['total_paid'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Lunas ke supplier</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Sisa Hutang Supplier</span>
                        <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 text-warning-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-warning-600 dark:text-warning-400">
                        Rp {{ number_format($kpi['total_debt'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Kewajiban bayar</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Barang Sudah Diterima</span>
                        <x-filament::icon icon="heroicon-o-archive-box-arrow-down" class="w-5 h-5 text-info-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-info-600 dark:text-info-400">
                        {{ $kpi['received_count'] ?? 0 }} PO
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Masuk fisik ke gudang</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Riwayat Pengadaan Barang Supplier</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-medium">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Kode PO</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Supplier</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3 text-right">Total Belanja</th>
                                <th class="px-4 py-3 text-right">Sudah Dibayar</th>
                                <th class="px-4 py-3 text-right">Sisa Hutang</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($reportData['purchases'] ?? [] as $index => $p)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ $p->code }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($p->date)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $p->supplier?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $p->branch?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-success-600 font-medium">Rp {{ number_format($p->paid_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-warning-600 font-medium">Rp {{ number_format($p->remaining_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $p->status === 'received' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : ($p->status === 'ordered' ? 'bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400') }}">
                                            {{ strtoupper($p->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data pembelian supplier pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Script untuk membuka jendela cetak di tab baru --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-print-tab', (event) => {
                if (event.url) {
                    window.open(event.url, '_blank');
                }
            });
        });
    </script>
</x-filament-panels::page>
