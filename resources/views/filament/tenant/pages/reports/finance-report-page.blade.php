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
                wire:click="setTab('cashflow')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'cashflow' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-arrows-right-left" class="w-4 h-4" />
                    <span>Arus Kas & Mutasi</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('profit_loss')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'profit_loss' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-chart-pie" class="w-4 h-4" />
                    <span>Estimasi Laba Rugi</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('receivables')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'receivables' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-document-text" class="w-4 h-4" />
                    <span>Piutang Pelanggan</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('payables')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'payables' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-shopping-bag" class="w-4 h-4" />
                    <span>Hutang Supplier (PO)</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('expenses')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'expenses' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-credit-card" class="w-4 h-4" />
                    <span>Beban Operasional</span>
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
        $summary = $reportData['summary'] ?? [];
    @endphp

    {{-- KONTEN TAB 1: ARUS KAS & MUTASI --}}
    @if ($activeTab === 'cashflow')
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Kas Masuk (In)</div>
                    <div class="text-2xl font-bold text-success-600 dark:text-success-400 mt-1">Rp {{ number_format($summary['total_cash_in'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Pelunasan servis & DP masuk</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Kas Keluar (Out)</div>
                    <div class="text-2xl font-bold text-danger-600 dark:text-danger-400 mt-1">Rp {{ number_format($summary['total_cash_out'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Operasional: Rp {{ number_format($summary['total_expenses'] ?? 0, 0, ',', '.') }}</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Arus Kas Bersih (Net Cash)</div>
                    <div class="text-2xl font-bold {{ ($summary['net_cash_flow'] ?? 0) >= 0 ? 'text-primary-600 dark:text-primary-400' : 'text-danger-600 dark:text-danger-400' }} mt-1">
                        Rp {{ number_format($summary['net_cash_flow'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">Surplus / defisit periode ini</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Transaksi Mutasi</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($summary['total_transactions'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Refund: Rp {{ number_format($summary['total_refunds'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            {{-- Rekap per Metode Pembayaran --}}
            @if (!empty($reportData['methods']))
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <h3 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-3">Rekapitulasi Menurut Metode Pembayaran</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach ($reportData['methods'] as $methodName => $vals)
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 border border-gray-100 dark:border-gray-800">
                                <div class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $methodName }}</div>
                                <div class="flex justify-between text-[11px] text-gray-500 mt-1.5">
                                    <span>Masuk:</span>
                                    <span class="text-success-600 font-medium">+Rp {{ number_format($vals['in'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-[11px] text-gray-500 mt-0.5">
                                    <span>Keluar:</span>
                                    <span class="text-danger-600 font-medium">-Rp {{ number_format($vals['out'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-xs font-bold border-t border-gray-200 dark:border-gray-700 pt-1 mt-1">
                                    <span>Net:</span>
                                    <span class="{{ $vals['net'] >= 0 ? 'text-primary-600' : 'text-danger-600' }}">Rp {{ number_format($vals['net'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Table Transaksi Kas --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Rincian Buku Kas Masuk & Keluar</h3>
                    <span class="text-xs text-gray-500">{{ count($reportData['rows'] ?? []) }} Transaksi</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3">Jenis Aliran</th>
                                <th class="px-4 py-3">No Referensi</th>
                                <th class="px-4 py-3">Keterangan Transaksi</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3">Metode Bayar</th>
                                <th class="px-4 py-3 text-right">Nominal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($reportData['rows'] ?? [] as $row)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $row['date'] }}</td>
                                    <td class="px-4 py-3">
                                        <x-filament::badge :color="$row['type'] === 'in' ? 'success' : 'danger'">
                                            {{ $row['type_label'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $row['reference'] }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-sm truncate" title="{{ $row['description'] }}">
                                        {{ $row['description'] }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $row['branch'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $row['method'] }}</td>
                                    <td class="px-4 py-3 text-right font-bold {{ $row['type'] === 'in' ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                        {{ $row['type'] === 'in' ? '+' : '-' }}Rp {{ number_format($row['amount'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada transaksi arus kas pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 2: ESTIMASI LABA RUGI --}}
    @if ($activeTab === 'profit_loss')
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Omset Pendapatan</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Sudah Terbayar: Rp {{ number_format($summary['total_paid_revenue'] ?? 0, 0, ',', '.') }}</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">HPP Modal Suku Cadang</div>
                    <div class="text-2xl font-bold text-warning-600 dark:text-warning-400 mt-1">Rp {{ number_format($summary['total_cogs'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Biaya modal barang terjual</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Laba Kotor (Gross Profit)</div>
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">Rp {{ number_format($summary['gross_profit'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-primary-600 mt-0.5">Margin Kotor: {{ $summary['gross_margin_pct'] ?? 0 }}%</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Laba Bersih Operasional</div>
                    <div class="text-2xl font-bold {{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }} mt-1">
                        Rp {{ number_format($summary['net_profit'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">Margin Bersih: {{ $summary['net_margin_pct'] ?? 0 }}%</div>
                </div>
            </div>

            {{-- 2-Column: Tabel Laba Rugi Pos Keuangan & Distribusi Beban Operasional --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Pos Laba Rugi --}}
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Laporan Laba Rugi Sederhana</h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Pendapatan Servis & Sparepart (Faktur)</span>
                            <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-100 dark:border-gray-800 text-danger-600">
                            <span>(-) Harga Pokok Penjualan (HPP Sparepart Terjual)</span>
                            <span class="font-medium">-Rp {{ number_format($summary['total_cogs'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 bg-gray-50 dark:bg-gray-800/50 px-3 rounded-lg font-bold">
                            <span class="text-gray-800 dark:text-gray-200">(=) Laba Kotor (Gross Profit)</span>
                            <span class="text-primary-600">Rp {{ number_format($summary['gross_profit'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-100 dark:border-gray-800 text-danger-600">
                            <span>(-) Total Beban Biaya Operasional Toko</span>
                            <span class="font-medium">-Rp {{ number_format($summary['total_operating_expenses'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2.5 bg-primary-50 dark:bg-primary-950/30 px-3 rounded-lg font-bold text-sm">
                            <span class="text-primary-900 dark:text-primary-200">(=) Estimasi Laba Bersih Operasional</span>
                            <span class="{{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                Rp {{ number_format($summary['net_profit'] ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Distribusi Beban Operasional --}}
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm space-y-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center justify-between">
                        <span>Distribusi Beban Biaya Operasional</span>
                        <span class="text-xs text-gray-500">Total: Rp {{ number_format($summary['total_operating_expenses'] ?? 0, 0, ',', '.') }}</span>
                    </h3>
                    <div class="space-y-2.5 pt-1">
                        @forelse ($reportData['expense_breakdown'] ?? [] as $eb)
                            <div>
                                <div class="flex justify-between text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    <span>{{ $eb['name'] }}</span>
                                    <span>Rp {{ number_format($eb['amount'], 0, ',', '.') }} ({{ $eb['percentage'] }}%)</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                    <div class="bg-danger-500 h-2 rounded-full" style="width: {{ $eb['percentage'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 py-6 text-center">Belum ada pengeluaran operasional.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 3: BUKU PIUTANG PELANGGAN --}}
    @if ($activeTab === 'receivables')
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Sisa Piutang Tertahan</div>
                    <div class="text-2xl font-bold text-danger-600 dark:text-danger-400 mt-1">Rp {{ number_format($summary['total_receivables'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Dari {{ $summary['total_invoices_count'] ?? 0 }} invoice belum lunas</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Piutang Melewati Jatuh Tempo</div>
                    <div class="text-2xl font-bold text-warning-600 dark:text-warning-400 mt-1">Rp {{ number_format($summary['overdue_amount'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-warning-600 mt-0.5">{{ $summary['overdue_count'] ?? 0 }} invoice overdue</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Nilai Tagihan Terbit</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($summary['total_invoice_amount'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Total nilai transaksi awal</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Tingkat Pelunasan Tagihan</div>
                    <div class="text-2xl font-bold text-success-600 dark:text-success-400 mt-1">{{ $summary['recovery_rate'] ?? 0 }}%</div>
                    <div class="text-xs text-success-600 mt-0.5">Sudah dibayar: Rp {{ number_format($summary['total_paid_amount'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            {{-- Table Piutang --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Daftar Tagihan & Piutang Pelanggan</h3>
                    <span class="text-xs text-gray-500">{{ count($reportData['rows'] ?? []) }} Invoice Belum Lunas</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">No Invoice</th>
                                <th class="px-4 py-3">Tiket Servis</th>
                                <th class="px-4 py-3">Pelanggan</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3">Tgl Invoice</th>
                                <th class="px-4 py-3">Jatuh Tempo</th>
                                <th class="px-4 py-3 text-right">Total Tagihan</th>
                                <th class="px-4 py-3 text-right">Sudah Dibayar</th>
                                <th class="px-4 py-3 text-right">Sisa Piutang</th>
                                <th class="px-4 py-3 text-center">Aksi Tagih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($reportData['rows'] ?? [] as $r)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="px-4 py-3 font-semibold text-primary-600">{{ $r['invoice_number'] }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $r['repair_code'] }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $r['client_name'] }}</div>
                                        <div class="text-gray-400 text-[11px]">{{ $r['client_whatsapp'] !== '-' ? $r['client_whatsapp'] : $r['client_phone'] }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $r['branch_name'] }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $r['invoice_date'] }}</td>
                                    <td class="px-4 py-3">
                                        @if ($r['is_overdue'])
                                            <span class="inline-flex items-center gap-1 text-danger-600 font-bold">
                                                <x-filament::icon icon="heroicon-m-exclamation-circle" class="w-3.5 h-3.5 inline" />
                                                {{ $r['due_date'] }} ({{ $r['days_overdue'] }} hr)
                                            </span>
                                        @else
                                            <span class="text-gray-600 dark:text-gray-400">{{ $r['due_date'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">Rp {{ number_format($r['grand_total'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-success-600">Rp {{ number_format($r['paid_amount'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-danger-600 dark:text-danger-400">
                                        Rp {{ number_format($r['remaining_amount'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($r['wa_link'])
                                            <a href="{{ $r['wa_link'] }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-success-50 text-success-700 hover:bg-success-100 dark:bg-success-950/40 dark:text-success-300 rounded font-semibold text-[11px]" title="Kirim Pengingat Pembayaran via WhatsApp">
                                                <x-filament::icon icon="heroicon-m-chat-bubble-left-ellipsis" class="w-3.5 h-3.5" />
                                                <span>Kirim WA</span>
                                            </a>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada piutang pelanggan yang tertunda pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 4: BUKU HUTANG SUPPLIER --}}
    @if ($activeTab === 'payables')
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Sisa Hutang Supplier</div>
                    <div class="text-2xl font-bold text-danger-600 dark:text-danger-400 mt-1">Rp {{ number_format($summary['total_payables'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $summary['total_pos_count'] ?? 0 }} transaksi PO belum lunas</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Nilai Pembelian PO</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($summary['total_po_value'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Total komitmen belanja supplier</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Sudah Dibayarkan Toko</div>
                    <div class="text-2xl font-bold text-success-600 dark:text-success-400 mt-1">Rp {{ number_format($summary['total_paid_to_suppliers'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-success-600 mt-0.5">Uang terbayar ke supplier</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Status Kewajiban Toko</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                        {{ ($summary['total_payables'] ?? 0) > 0 ? 'Ada Tagihan Tempo' : 'Lunas Bersih' }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">Kewajiban pengadaan barang</div>
                </div>
            </div>

            {{-- Table Hutang --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Daftar Tagihan Hutang ke Supplier (PO)</h3>
                    <span class="text-xs text-gray-500">{{ count($reportData['rows'] ?? []) }} PO Belum Lunas</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">Kode PO</th>
                                <th class="px-4 py-3">Tanggal PO</th>
                                <th class="px-4 py-3">Nama Supplier</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3 text-right">Total Pembelian</th>
                                <th class="px-4 py-3 text-right">Sudah Dibayar</th>
                                <th class="px-4 py-3 text-right">Sisa Hutang Toko</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($reportData['rows'] ?? [] as $p)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="px-4 py-3 font-semibold text-primary-600">{{ $p['code'] }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $p['date'] }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $p['supplier_name'] }}</div>
                                        <div class="text-gray-400 text-[11px]">{{ $p['supplier_phone'] }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $p['branch_name'] }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($p['total'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-success-600">Rp {{ number_format($p['paid_amount'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-danger-600 dark:text-danger-400">
                                        Rp {{ number_format($p['remaining_amount'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <x-filament::badge color="warning">
                                            {{ $p['status'] }}
                                        </x-filament::badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada tagihan hutang supplier yang tertunda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 5: BEBAN OPERASIONAL TOKO --}}
    @if ($activeTab === 'expenses')
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Beban Operasional</div>
                    <div class="text-2xl font-bold text-danger-600 dark:text-danger-400 mt-1">Rp {{ number_format($summary['total_expenses'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Dari {{ $summary['total_transactions'] ?? 0 }} nota pengeluaran</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Kategori Beban Terbesar</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $summary['top_category_name'] ?? '-' }}</div>
                    <div class="text-xs text-danger-600 mt-0.5">Rp {{ number_format($summary['top_category_amount'] ?? 0, 0, ',', '.') }} ({{ $summary['top_category_pct'] ?? 0 }}%)</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Jumlah Kategori Pengeluaran</div>
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ count($reportData['categories'] ?? []) }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Pos alokasi biaya toko</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Rata-rata per Nota</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        Rp {{ number_format(($summary['total_transactions'] ?? 0) > 0 ? ($summary['total_expenses'] / $summary['total_transactions']) : 0, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">Rata-rata biaya transaksi</div>
                </div>
            </div>

            {{-- Table Beban Pengeluaran --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Daftar Nota Beban & Pengeluaran Toko</h3>
                    <span class="text-xs text-gray-500">{{ count($reportData['rows'] ?? []) }} Nota</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">No Referensi</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3">Kategori Biaya</th>
                                <th class="px-4 py-3">Metode Bayar</th>
                                <th class="px-4 py-3">Keterangan</th>
                                <th class="px-4 py-3 text-right">Nominal (Rp)</th>
                                <th class="px-4 py-3">Dicatat Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($reportData['rows'] ?? [] as $e)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="px-4 py-3 text-gray-500">{{ $e['date'] }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $e['reference_number'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $e['branch_name'] }}</td>
                                    <td class="px-4 py-3">
                                        <x-filament::badge color="gray">{{ $e['category_name'] }}</x-filament::badge>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $e['payment_method_name'] }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-sm truncate" title="{{ $e['description'] }}">
                                        {{ $e['description'] }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-danger-600 dark:text-danger-400">
                                        Rp {{ number_format($e['amount'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $e['created_by'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada catatan pengeluaran operasional pada periode ini.
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
