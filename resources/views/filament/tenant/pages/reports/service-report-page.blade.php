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
                wire:click="setTab('operations')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'operations' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-wrench-screwdriver" class="w-4 h-4" />
                    <span>Operasional Servis</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('technicians')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'technicians' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-user-group" class="w-4 h-4" />
                    <span>Kinerja & Komisi Teknisi</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('fault_trends')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'fault_trends' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-chart-bar" class="w-4 h-4" />
                    <span>Tren Kerusakan & Perangkat</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('crm')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'crm' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-users" class="w-4 h-4" />
                    <span>Analitik Pelanggan & CRM</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('warranty')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all {{ $activeTab === 'warranty' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-shield-check" class="w-4 h-4" />
                    <span>Klaim Garansi & RMA</span>
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

    {{-- KONTEN TAB 1: OPERASIONAL SERVIS --}}
    @if ($activeTab === 'operations')
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Tiket Masuk</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($summary['total_tickets'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ number_format($summary['total_devices'] ?? 0) }} total perangkat</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Sudah Diambil (Delivered)</div>
                    <div class="text-2xl font-bold text-success-600 dark:text-success-400 mt-1">{{ number_format($summary['delivered_count'] ?? 0) }}</div>
                    <div class="text-xs text-success-600 mt-0.5">{{ $summary['completion_rate'] ?? 0 }}% tingkat penyelesaian</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Rata-rata Durasi Selesai (TAT)</div>
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $summary['avg_tat_days'] ?? 0 }} <span class="text-sm font-normal text-gray-500">Hari</span></div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $summary['in_service_count'] ?? 0 }} tiket sedang diproses</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Omset Servis</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Terbayar: Rp {{ number_format($summary['total_paid'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Rincian Tiket Servis</h3>
                    <span class="text-xs text-gray-500">{{ count($reportData['rows'] ?? []) }} Tiket Ditemukan</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">No Tiket</th>
                                <th class="px-4 py-3">Tgl Masuk</th>
                                <th class="px-4 py-3">Pelanggan</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3">Rincian Perangkat</th>
                                <th class="px-4 py-3">Teknisi</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Biaya (Rp)</th>
                                <th class="px-4 py-3 text-center">Durasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($reportData['rows'] ?? [] as $row)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="px-4 py-3 font-semibold text-primary-600 dark:text-primary-400">{{ $row['code'] }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $row['date'] }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $row['client_name'] }}</div>
                                        <div class="text-gray-400 text-[11px]">{{ $row['client_phone'] }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $row['branch_name'] }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs truncate" title="{{ $row['devices_summary'] }}">
                                        {{ $row['devices_summary'] }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $row['technicians'] }}</td>
                                    <td class="px-4 py-3">
                                        <x-filament::badge :color="$row['status_color']">
                                            {{ $row['status_label'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                        Rp {{ number_format($row['grand_total'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-500">
                                        {{ $row['tat_days'] !== null ? $row['tat_days'] . ' hari' : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada tiket servis yang sesuai dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 2: KINERJA & KOMISI TEKNISI --}}
    @if ($activeTab === 'technicians')
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Teknisi Aktif</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($summary['total_technicians'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Penugasan dalam periode ini</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Unit Ditangani</div>
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ number_format($summary['total_jobs_assigned'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ number_format($summary['total_completed'] ?? 0) }} unit berhasil selesai</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Tingkat Keberhasilan (Success Rate)</div>
                    <div class="text-2xl font-bold text-success-600 dark:text-success-400 mt-1">{{ $summary['overall_success_rate'] ?? 0 }}%</div>
                    <div class="text-xs text-success-600 mt-0.5">Rasio pengerjaan sukses</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Komisi Teknisi</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($summary['total_commission_paid'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Akumulasi komisi periode ini</div>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Daftar Produktivitas & Komisi Teknisi</h3>
                    <span class="text-xs text-gray-500">{{ count($reportData['rows'] ?? []) }} Teknisi</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">Nama Teknisi</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3 text-center">Ditugaskan</th>
                                <th class="px-4 py-3 text-center">Selesai</th>
                                <th class="px-4 py-3 text-center">Dalam Proses</th>
                                <th class="px-4 py-3 text-center">Batal / Gagal</th>
                                <th class="px-4 py-3 text-center">Success Rate</th>
                                <th class="px-4 py-3 text-center">Rata-rata Durasi</th>
                                <th class="px-4 py-3 text-right">Total Komisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($reportData['rows'] ?? [] as $row)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $row['technician_name'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $row['branch_name'] }}</td>
                                    <td class="px-4 py-3 text-center font-medium">{{ $row['total_assigned'] }}</td>
                                    <td class="px-4 py-3 text-center text-success-600 font-semibold">{{ $row['completed_count'] }}</td>
                                    <td class="px-4 py-3 text-center text-warning-600">{{ $row['in_progress_count'] }}</td>
                                    <td class="px-4 py-3 text-center text-danger-600">{{ $row['canceled_count'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <x-filament::badge :color="$row['success_rate'] >= 80 ? 'success' : ($row['success_rate'] >= 50 ? 'warning' : 'danger')">
                                            {{ $row['success_rate'] }}%
                                        </x-filament::badge>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-500">{{ $row['avg_tat_days'] }} hari</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($row['total_commission'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data pengerjaan teknisi pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 3: TREN KERUSAKAN & PERANGKAT --}}
    @if ($activeTab === 'fault_trends')
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Unit Dianalisis</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($summary['total_devices'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $summary['total_brands'] ?? 0 }} merek berbeda</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Merek Paling Sering (Top Brand)</div>
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $summary['top_brand'] ?? '-' }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $summary['top_brand_pct'] ?? 0 }}% dari total unit</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Tipe Perangkat Terbanyak</div>
                    <div class="text-2xl font-bold text-success-600 dark:text-success-400 mt-1">{{ $summary['top_device_type'] ?? '-' }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Kategori perangkat dominan</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Model Terbanyak (Top Model)</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white mt-1 truncate" title="{{ $summary['top_model'] ?? '-' }}">
                        {{ $summary['top_model'] ?? '-' }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">Paling sering diservis</div>
                </div>
            </div>

            {{-- 2-Column Grid: Brand Distribution & Device Type Distribution --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Distribusi Brand --}}
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm space-y-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center justify-between">
                        <span>Distribusi Menurut Brand (Merek)</span>
                        <span class="text-xs text-gray-500">{{ count($reportData['brands'] ?? []) }} Brand</span>
                    </h3>
                    <div class="space-y-2 pt-1">
                        @forelse (array_slice($reportData['brands'] ?? [], 0, 7) as $b)
                            <div>
                                <div class="flex justify-between text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    <span>{{ $b['name'] }}</span>
                                    <span>{{ $b['count'] }} Unit ({{ $b['percentage'] }}%)</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                    <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $b['percentage'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 py-4 text-center">Belum ada data brand.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Distribusi Tipe Perangkat --}}
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm space-y-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center justify-between">
                        <span>Distribusi Menurut Tipe Perangkat</span>
                        <span class="text-xs text-gray-500">{{ count($reportData['types'] ?? []) }} Tipe</span>
                    </h3>
                    <div class="space-y-2 pt-1">
                        @forelse ($reportData['types'] ?? [] as $t)
                            <div>
                                <div class="flex justify-between text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    <span>{{ $t['name'] }}</span>
                                    <span>{{ $t['count'] }} Unit ({{ $t['percentage'] }}%)</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                    <div class="bg-success-500 h-2 rounded-full" style="width: {{ $t['percentage'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 py-4 text-center">Belum ada data tipe perangkat.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Table: Top Models --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top 15 Model Perangkat Paling Sering Diservis</h3>
                    <span class="text-xs text-gray-500">Peringkat Frekuensi Kerusakan</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Model / Seri Perangkat</th>
                                <th class="px-4 py-3 text-center">Jumlah Kasus</th>
                                <th class="px-4 py-3 text-center">Persentase</th>
                                <th class="px-4 py-3">Sampel Masalah / Keluhan Umum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($reportData['top_models'] ?? [] as $idx => $m)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="px-4 py-3 font-semibold text-gray-400">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $m['model'] }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-primary-600 dark:text-primary-400">{{ $m['count'] }}</td>
                                    <td class="px-4 py-3 text-center text-gray-500">{{ $m['percentage'] }}%</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-md truncate" title="{{ $m['sample_problems'] }}">
                                        {{ $m['sample_problems'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data unit perangkat untuk dianalisis.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 4: ANALITIK PELANGGAN & CRM --}}
    @if ($activeTab === 'crm')
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Pelanggan Unik</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($summary['total_unique_clients'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Pelanggan terlayani periode ini</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Pelanggan Berulang (Repeat)</div>
                    <div class="text-2xl font-bold text-success-600 dark:text-success-400 mt-1">{{ number_format($summary['repeat_clients_count'] ?? 0) }}</div>
                    <div class="text-xs text-success-600 mt-0.5">{{ $summary['repeat_rate'] ?? 0 }}% Repeat Rate (Loyalitas)</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Rata-rata Belanja / Pelanggan</div>
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">Rp {{ number_format($summary['avg_spend_per_client'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Average Customer LTV</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Omset Servis CRM</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Kontribusi nilai transaksi</div>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Daftar Pelanggan & Analitik Retensi (CRM)</h3>
                    <span class="text-xs text-gray-500">Diurutkan Menurut Nilai Belanja Tertinggi</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">Nama Pelanggan</th>
                                <th class="px-4 py-3">Kontak WhatsApp</th>
                                <th class="px-4 py-3">Alamat</th>
                                <th class="px-4 py-3 text-center">Jml Tiket</th>
                                <th class="px-4 py-3 text-center">Jml Unit</th>
                                <th class="px-4 py-3 text-right">Total Belanja (LTV)</th>
                                <th class="px-4 py-3 text-center">Kunjungan Pertama</th>
                                <th class="px-4 py-3 text-center">Kunjungan Terakhir</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($reportData['rows'] ?? [] as $client)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $client['name'] }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-gray-600 dark:text-gray-300">{{ $client['whatsapp'] !== '-' ? $client['whatsapp'] : $client['phone'] }}</span>
                                            @if ($client['wa_link'])
                                                <a href="{{ $client['wa_link'] }}" target="_blank" class="text-success-600 hover:text-success-700 font-medium" title="Kirim Pesan WhatsApp">
                                                    <x-filament::icon icon="heroicon-m-chat-bubble-left-ellipsis" class="w-4 h-4 inline" />
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 max-w-xs truncate" title="{{ $client['address'] }}">
                                        {{ $client['address'] }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium">{{ $client['ticket_count'] }}</td>
                                    <td class="px-4 py-3 text-center text-gray-500">{{ $client['device_count'] }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($client['total_spend'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-500">{{ $client['first_visit'] }}</td>
                                    <td class="px-4 py-3 text-center text-gray-500">{{ $client['last_visit'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($client['is_repeat'])
                                            <x-filament::badge color="success">Pelanggan Setia</x-filament::badge>
                                        @else
                                            <x-filament::badge color="gray">Pelanggan Baru</x-filament::badge>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data pelanggan yang sesuai dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 5: KLAIM GARANSI & RMA --}}
    @if ($activeTab === 'warranty')
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Klaim Garansi Masuk</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($summary['total_claims'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Pengajuan garansi periode ini</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Tingkat Retur Servis (RMA Rate)</div>
                    <div class="text-2xl font-bold text-warning-600 dark:text-warning-400 mt-1">{{ $summary['rma_rate'] ?? 0 }}%</div>
                    <div class="text-xs text-gray-500 mt-0.5">Dari {{ number_format($summary['delivered_repairs_count'] ?? 0) }} servis selesai</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Klaim Selesai vs Ditolak</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        <span class="text-success-600">{{ $summary['completed_count'] ?? 0 }}</span> / <span class="text-danger-600">{{ $summary['rejected_count'] ?? 0 }}</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $summary['pending_count'] ?? 0 }} masih dalam proses</div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">Total Pengembalian Dana (Refund)</div>
                    <div class="text-2xl font-bold text-danger-600 dark:text-danger-400 mt-1">Rp {{ number_format($summary['total_refund'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Kompensasi refund garansi</div>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Daftar Pengajuan Klaim Garansi & Retur (RMA)</h3>
                    <span class="text-xs text-gray-500">{{ count($reportData['rows'] ?? []) }} Klaim</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">Tgl Klaim</th>
                                <th class="px-4 py-3">Tiket Asal</th>
                                <th class="px-4 py-3">Tiket Baru RMA</th>
                                <th class="px-4 py-3">Pelanggan</th>
                                <th class="px-4 py-3">Komponen / Jasa</th>
                                <th class="px-4 py-3">Keluhan Masalah</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Refund</th>
                                <th class="px-4 py-3">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($reportData['rows'] ?? [] as $claim)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="px-4 py-3 text-gray-500">{{ $claim['claim_date'] }}</td>
                                    <td class="px-4 py-3 font-semibold text-primary-600">{{ $claim['original_ticket'] }}</td>
                                    <td class="px-4 py-3 font-semibold text-warning-600">{{ $claim['new_ticket'] }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $claim['client_name'] }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $claim['item_name'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $claim['problems'] }}">
                                        {{ $claim['problems'] }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-filament::badge :color="$claim['status_color']">
                                            {{ $claim['status_label'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold {{ $claim['refund_amount'] > 0 ? 'text-danger-600' : 'text-gray-500' }}">
                                        Rp {{ number_format($claim['refund_amount'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $claim['handler_name'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada klaim garansi yang diajukan pada periode ini.
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
