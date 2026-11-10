<x-filament-panels::page>
    {{-- Form Filter Terpadu --}}
    <form wire:submit.prevent class="space-y-4">
        {{ $this->form }}
    </form>

    {{-- Navigasi Tab & Tombol Aksi (Export / Print) --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-800 pb-3">
        {{-- Tab Switcher --}}
        <div class="flex items-center space-x-1 sm:space-x-2 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
            <button
                type="button"
                wire:click="setTab('attendance')"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-all {{ $activeTab === 'attendance' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4" />
                    <span>Presensi & Jam Kerja</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('leave')"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-all {{ $activeTab === 'leave' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-calendar-days" class="w-4 h-4" />
                    <span>Cuti & Izin</span>
                </div>
            </button>

            <button
                type="button"
                wire:click="setTab('payroll')"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-all {{ $activeTab === 'payroll' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-banknotes" class="w-4 h-4" />
                    <span>Penggajian & Komisi</span>
                </div>
            </button>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
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

    {{-- KONTEN TAB 1: PRESENSI & JAM KERJA --}}
    @if ($activeTab === 'attendance')
        <div class="space-y-6">
            {{-- KPI Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Kehadiran Efektif</span>
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-success-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                        {{ ($kpi['present_count'] ?? 0) + ($kpi['late_count'] ?? 0) }} Hari
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Tepat Waktu: {{ $kpi['present_count'] ?? 0 }} hari</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Total Terlambat</span>
                        <x-filament::icon icon="heroicon-o-exclamation-circle" class="w-5 h-5 text-warning-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-warning-600 dark:text-warning-400">
                        {{ $kpi['late_count'] ?? 0 }} Kali
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Akumulasi: {{ number_format($kpi['late_minutes'] ?? 0) }} menit</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Total Lembur (OT)</span>
                        <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 text-primary-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ number_format(($kpi['overtime_minutes'] ?? 0) / 60, 1) }} Jam
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ number_format($kpi['overtime_minutes'] ?? 0) }} menit lembur</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Cuti & Alpha</span>
                        <x-filament::icon icon="heroicon-o-user-minus" class="w-5 h-5 text-danger-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-danger-600 dark:text-danger-400">
                        {{ ($kpi['leave_count'] ?? 0) }} / {{ ($kpi['absent_count'] ?? 0) }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Cuti: {{ $kpi['leave_count'] ?? 0 }} | Alpha: {{ $kpi['absent_count'] ?? 0 }}</p>
                </div>
            </div>

            {{-- Tabel Rincian Kehadiran per Karyawan --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Rekapitulasi Kehadiran per Karyawan</h3>
                    <span class="text-xs text-gray-500">Total: {{ count($reportData['rows'] ?? []) }} Karyawan</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-medium">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Nama Karyawan</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3 text-center">Tepat Waktu</th>
                                <th class="px-4 py-3 text-center">Terlambat</th>
                                <th class="px-4 py-3 text-center">Pulang Cepat</th>
                                <th class="px-4 py-3 text-center">Lembur</th>
                                <th class="px-4 py-3 text-center">Cuti/Izin</th>
                                <th class="px-4 py-3 text-center">Alpha</th>
                                <th class="px-4 py-3 text-right">% Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($reportData['rows'] ?? [] as $index => $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $row['name'] }}</div>
                                        <div class="text-xs text-gray-400">{{ $row['code_pegawai'] }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ $row['branch_name'] }}</td>
                                    <td class="px-4 py-3 text-center text-success-600 font-semibold">{{ $row['present_days'] }} hr</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="{{ $row['late_days'] > 0 ? 'text-warning-600 font-semibold' : 'text-gray-400' }}">
                                            {{ $row['late_days'] }} hr
                                        </span>
                                        @if ($row['late_minutes'] > 0)
                                            <span class="text-xs text-gray-400 block">({{ $row['late_minutes'] }}m)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-500">
                                        {{ $row['early_minutes'] > 0 ? $row['early_minutes'] . 'm' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-primary-600 font-medium">
                                        {{ $row['overtime_minutes'] > 0 ? number_format($row['overtime_minutes'] / 60, 1) . ' jam' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">
                                        {{ $row['leave_days'] > 0 ? $row['leave_days'] . ' hr' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="{{ $row['absent_days'] > 0 ? 'text-danger-600 font-bold' : 'text-gray-400' }}">
                                            {{ $row['absent_days'] > 0 ? $row['absent_days'] . ' hr' : '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $row['attendance_rate'] >= 90 ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400' : ($row['attendance_rate'] >= 75 ? 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400' : 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400') }}">
                                            {{ $row['attendance_rate'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data presensi pada periode dan filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 2: CUTI & IZIN --}}
    @if ($activeTab === 'leave')
        <div class="space-y-6">
            {{-- KPI Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Total Pengajuan</span>
                        <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5 text-primary-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $kpi['total_requests'] ?? 0 }} Pengajuan
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Dalam periode filter</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Disetujui (Approved)</span>
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-success-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">
                        {{ $kpi['approved_requests'] ?? 0 }} Pengajuan
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Total hari: {{ number_format($kpi['total_days_used'] ?? 0) }} hari</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Menunggu Approval</span>
                        <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 text-warning-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-warning-600 dark:text-warning-400">
                        {{ $kpi['pending_requests'] ?? 0 }} Menunggu
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Perlu tindak lanjut</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Sisa Kuota Cuti Tim</span>
                        <x-filament::icon icon="heroicon-o-ticket" class="w-5 h-5 text-info-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-info-600 dark:text-info-400">
                        {{ number_format($kpi['total_quota_remaining'] ?? 0) }} Hari
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Akumulasi sisa kuota</p>
                </div>
            </div>

            {{-- Tabel Riwayat Pengajuan Cuti --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Riwayat Pengajuan Cuti & Izin</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-medium">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Nama Karyawan</th>
                                <th class="px-4 py-3">Jenis Cuti</th>
                                <th class="px-4 py-3">Rentang Tanggal</th>
                                <th class="px-4 py-3 text-center">Durasi</th>
                                <th class="px-4 py-3">Alasan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3">Approver</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($reportData['requests'] ?? [] as $index => $req)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                        {{ $req->employee?->user?->name ?? 'Karyawan #' . $req->employee_id }}
                                    </td>
                                    <td class="px-4 py-3">{{ $req->leaveType?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="text-xs">
                                            {{ \Carbon\Carbon::parse($req->start_date)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">{{ $req->total_days }} hari</td>
                                    <td class="px-4 py-3 max-w-xs truncate text-xs text-gray-500">{{ $req->reason }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $req->status === 'approved' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : ($req->status === 'pending' ? 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400' : 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400') }}">
                                            {{ ucfirst($req->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ $req->approvedBy?->name ?? $req->rejectedBy?->name ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada riwayat pengajuan cuti pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tabel Sisa Kuota Cuti Karyawan --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Status Kuota Cuti Karyawan</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-medium">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Nama Karyawan</th>
                                <th class="px-4 py-3">Jenis Cuti</th>
                                <th class="px-4 py-3 text-center">Kuota Awal</th>
                                <th class="px-4 py-3 text-center">Terpakai</th>
                                <th class="px-4 py-3 text-center">Sisa Saldo</th>
                                <th class="px-4 py-3">Periode Kuota</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($reportData['balances'] ?? [] as $index => $b)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                        {{ $b->employee?->user?->name ?? 'Karyawan #' . $b->employee_id }}
                                    </td>
                                    <td class="px-4 py-3">{{ $b->leaveType?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center font-medium">{{ $b->quota }} hari</td>
                                    <td class="px-4 py-3 text-center font-medium text-warning-600">{{ $b->used }} hari</td>
                                    <td class="px-4 py-3 text-center font-bold text-success-600">{{ $b->remaining }} hari</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($b->period_start)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($b->period_end)->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data kuota cuti tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 3: PENGGAJIAN & KOMISI --}}
    @if ($activeTab === 'payroll')
        <div class="space-y-6">
            {{-- KPI Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Total Beban Gaji Bersih</span>
                        <x-filament::icon icon="heroicon-o-banknotes" class="w-5 h-5 text-primary-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-primary-600 dark:text-primary-400">
                        Rp {{ number_format($kpi['total_net_salary'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ $kpi['total_slips'] ?? 0 }} Slip Gaji dibuat</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Total Gaji Pokok</span>
                        <x-filament::icon icon="heroicon-o-wallet" class="w-5 h-5 text-gray-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($kpi['total_basic_salary'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Komponen pokok</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Tunjangan & Komisi</span>
                        <x-filament::icon icon="heroicon-o-arrow-trending-up" class="w-5 h-5 text-success-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">
                        Rp {{ number_format($kpi['total_allowance'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Termasuk komisi teknisi</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs font-medium uppercase">
                        <span>Status Pembayaran</span>
                        <x-filament::icon icon="heroicon-o-credit-card" class="w-5 h-5 text-info-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                        <span class="text-success-600">{{ $kpi['paid_count'] ?? 0 }} Paid</span> /
                        <span class="text-warning-600">{{ $kpi['draft_count'] ?? 0 }} Draft</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Total Potongan: Rp {{ number_format($kpi['total_deduction'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Tabel Slip Gaji --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Rincian Slip Gaji Karyawan</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-medium">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Periode</th>
                                <th class="px-4 py-3">Nama Karyawan</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3 text-right">Gaji Pokok</th>
                                <th class="px-4 py-3 text-right">Tunjangan & Komisi</th>
                                <th class="px-4 py-3 text-right">Potongan</th>
                                <th class="px-4 py-3 text-right">Gaji Bersih</th>
                                <th class="px-4 py-3 text-center">Metode Bayar</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($reportData['payrolls'] ?? [] as $index => $p)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $p->period }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                        {{ $p->employee?->user?->name ?? 'Karyawan #' . $p->employee_id }}
                                    </td>
                                    <td class="px-4 py-3">{{ $p->branch?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right">Rp {{ number_format($p->basic_salary, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-success-600 font-medium">+Rp {{ number_format($p->total_allowance, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-danger-600 font-medium">-Rp {{ number_format($p->total_deduction, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">Rp {{ number_format($p->net_salary, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $p->paymentMethod?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $p->status === 'paid' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400' }}">
                                            {{ strtoupper($p->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data slip gaji pada periode ini.
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
