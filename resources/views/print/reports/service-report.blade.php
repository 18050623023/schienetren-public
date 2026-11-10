<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Laporan Servis & Analitik - 
        {{ match($tab) {
            'technicians' => 'Kinerja & Komisi Teknisi',
            'fault_trends' => 'Tren Kerusakan & Perangkat',
            'crm' => 'Analitik Pelanggan & CRM',
            'warranty' => 'Klaim Garansi & Retur RMA',
            default => 'Operasional Tiket Servis',
        } }}
    </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #222;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 950px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            margin: 4px 0 0;
            font-size: 13px;
            font-weight: normal;
            color: #555;
        }
        .meta-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            background: #fafafa;
            border: 1px solid #ddd;
            padding: 10px 14px;
            border-radius: 4px;
        }
        .meta-box div {
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .kpi-grid {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }
        .kpi-card {
            flex: 1;
            border: 1px solid #ddd;
            background: #fbfbfb;
            padding: 8px 12px;
            border-radius: 4px;
            text-align: center;
        }
        .kpi-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 4px;
        }
        .kpi-val {
            font-size: 14px;
            font-weight: bold;
            color: #111;
        }
        .footer-signatures {
            margin-top: 36px;
            display: flex;
            justify-content: space-between;
            padding: 0 40px;
        }
        .signature-box {
            text-align: center;
            width: 180px;
        }
        .signature-line {
            margin-top: 55px;
            border-bottom: 1px solid #333;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Tombol Cetak Browser --}}
        <div class="no-print" style="margin-bottom: 16px; text-align: right;">
            <button onclick="window.print()" style="padding: 6px 14px; font-size: 12px; cursor: pointer; background: #2563eb; color: #fff; border: none; border-radius: 4px;">
                Cetak / Simpan PDF
            </button>
        </div>

        {{-- Header Dokumen --}}
        <div class="header">
            <h1>{{ tenant('name') ?? 'SCHIENETREN SERVICE & REPAIR' }}</h1>
            <h2>
                LAPORAN 
                {{ match($tab) {
                    'technicians' => 'KINERJA & KOMISI TEKNISI',
                    'fault_trends' => 'TREN KERUSAKAN & PERANGKAT',
                    'crm' => 'ANALITIK PELANGGAN & CRM',
                    'warranty' => 'KLAIM GARANSI & RETUR RMA',
                    default => 'OPERASIONAL TIKET SERVIS',
                } }}
            </h2>
        </div>

        {{-- Meta Filter Info --}}
        <div class="meta-box">
            <div>
                <strong>Cabang:</strong> {{ $branch?->name ?? 'Semua Cabang' }}<br>
                <strong>Periode:</strong> 
                {{ $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Awal' }} 
                s/d 
                {{ $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'Akhir' }}
            </div>
            <div style="text-align: right;">
                @if ($technician)
                    <strong>Teknisi:</strong> {{ $technician->employee?->name ?? '-' }}<br>
                @endif
                @if ($brand)
                    <strong>Brand:</strong> {{ $brand->name }}<br>
                @endif
                <strong>Dicetak Pada:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
            </div>
        </div>

        @php
            $summary = $reportData['summary'] ?? [];
        @endphp

        {{-- TAB 1: OPERASIONAL --}}
        @if ($tab === 'operations')
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-title">Total Tiket</div>
                    <div class="kpi-val">{{ number_format($summary['total_tickets'] ?? 0) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Sudah Diambil</div>
                    <div class="kpi-val">{{ number_format($summary['delivered_count'] ?? 0) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Rata-rata TAT</div>
                    <div class="kpi-val">{{ $summary['avg_tat_days'] ?? 0 }} Hari</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Total Omset</div>
                    <div class="kpi-val">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No Tiket</th>
                        <th>Tgl Masuk</th>
                        <th>Pelanggan</th>
                        <th>Cabang</th>
                        <th>Rincian Perangkat</th>
                        <th>Teknisi</th>
                        <th>Status</th>
                        <th class="text-right">Biaya (Rp)</th>
                        <th class="text-center">Durasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['rows'] ?? [] as $r)
                        <tr>
                            <td class="font-bold">{{ $r['code'] }}</td>
                            <td>{{ $r['date'] }}</td>
                            <td>{{ $r['client_name'] }}</td>
                            <td>{{ $r['branch_name'] }}</td>
                            <td>{{ $r['devices_summary'] }}</td>
                            <td>{{ $r['technicians'] }}</td>
                            <td>{{ $r['status_label'] }}</td>
                            <td class="text-right font-bold">Rp {{ number_format($r['grand_total'], 0, ',', '.') }}</td>
                            <td class="text-center">{{ $r['tat_days'] !== null ? $r['tat_days'] . ' hr' : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">Tidak ada data tiket servis.</td></tr>
                    @endforelse
                </tbody>
            </table>

        {{-- TAB 2: TEKNISI --}}
        @elseif ($tab === 'technicians')
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-title">Teknisi Aktif</div>
                    <div class="kpi-val">{{ number_format($summary['total_technicians'] ?? 0) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Total Ditugaskan</div>
                    <div class="kpi-val">{{ number_format($summary['total_jobs_assigned'] ?? 0) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Success Rate</div>
                    <div class="kpi-val">{{ $summary['overall_success_rate'] ?? 0 }}%</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Total Komisi</div>
                    <div class="kpi-val">Rp {{ number_format($summary['total_commission_paid'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Nama Teknisi</th>
                        <th>Cabang</th>
                        <th class="text-center">Ditugaskan</th>
                        <th class="text-center">Selesai</th>
                        <th class="text-center">Proses</th>
                        <th class="text-center">Batal</th>
                        <th class="text-center">Success Rate</th>
                        <th class="text-center">Rata2 Durasi</th>
                        <th class="text-right">Total Komisi (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['rows'] ?? [] as $t)
                        <tr>
                            <td class="font-bold">{{ $t['technician_name'] }}</td>
                            <td>{{ $t['branch_name'] }}</td>
                            <td class="text-center">{{ $t['total_assigned'] }}</td>
                            <td class="text-center">{{ $t['completed_count'] }}</td>
                            <td class="text-center">{{ $t['in_progress_count'] }}</td>
                            <td class="text-center">{{ $t['canceled_count'] }}</td>
                            <td class="text-center font-bold">{{ $t['success_rate'] }}%</td>
                            <td class="text-center">{{ $t['avg_tat_days'] }} hr</td>
                            <td class="text-right font-bold">Rp {{ number_format($t['total_commission'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">Tidak ada data pengerjaan teknisi.</td></tr>
                    @endforelse
                </tbody>
            </table>

        {{-- TAB 3: TREN KERUSAKAN --}}
        @elseif ($tab === 'fault_trends')
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-title">Unit Dianalisis</div>
                    <div class="kpi-val">{{ number_format($summary['total_devices'] ?? 0) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Top Brand</div>
                    <div class="kpi-val">{{ $summary['top_brand'] ?? '-' }} ({{ $summary['top_brand_pct'] ?? 0 }}%)</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Top Device Type</div>
                    <div class="kpi-val">{{ $summary['top_device_type'] ?? '-' }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Top Model</div>
                    <div class="kpi-val">{{ $summary['top_model'] ?? '-' }}</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Model / Seri Perangkat</th>
                        <th class="text-center">Jumlah Kasus</th>
                        <th class="text-center">Persentase</th>
                        <th>Sampel Keluhan / Masalah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['top_models'] ?? [] as $idx => $m)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="font-bold">{{ $m['model'] }}</td>
                            <td class="text-center font-bold">{{ $m['count'] }}</td>
                            <td class="text-center">{{ $m['percentage'] }}%</td>
                            <td>{{ $m['sample_problems'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Tidak ada data perangkat.</td></tr>
                    @endforelse
                </tbody>
            </table>

        {{-- TAB 4: CRM --}}
        @elseif ($tab === 'crm')
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-title">Pelanggan Unik</div>
                    <div class="kpi-val">{{ number_format($summary['total_unique_clients'] ?? 0) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Repeat Rate</div>
                    <div class="kpi-val">{{ $summary['repeat_rate'] ?? 0 }}%</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Rata2 Belanja</div>
                    <div class="kpi-val">Rp {{ number_format($summary['avg_spend_per_client'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Total Omset CRM</div>
                    <div class="kpi-val">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Telepon / WA</th>
                        <th>Alamat</th>
                        <th class="text-center">Tiket</th>
                        <th class="text-center">Unit</th>
                        <th class="text-right">Total Belanja (LTV)</th>
                        <th class="text-center">Kunjungan Pertama</th>
                        <th class="text-center">Kunjungan Terakhir</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['rows'] ?? [] as $c)
                        <tr>
                            <td class="font-bold">{{ $c['name'] }}</td>
                            <td>{{ $c['whatsapp'] !== '-' ? $c['whatsapp'] : $c['phone'] }}</td>
                            <td>{{ $c['address'] }}</td>
                            <td class="text-center">{{ $c['ticket_count'] }}</td>
                            <td class="text-center">{{ $c['device_count'] }}</td>
                            <td class="text-right font-bold">Rp {{ number_format($c['total_spend'], 0, ',', '.') }}</td>
                            <td class="text-center">{{ $c['first_visit'] }}</td>
                            <td class="text-center">{{ $c['last_visit'] }}</td>
                            <td class="text-center">{{ $c['is_repeat'] ? 'Pelanggan Setia' : 'Pelanggan Baru' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">Tidak ada data pelanggan.</td></tr>
                    @endforelse
                </tbody>
            </table>

        {{-- TAB 5: GARANSI --}}
        @elseif ($tab === 'warranty')
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-title">Total Klaim</div>
                    <div class="kpi-val">{{ number_format($summary['total_claims'] ?? 0) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">RMA Rate</div>
                    <div class="kpi-val">{{ $summary['rma_rate'] ?? 0 }}%</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Selesai / Tolak</div>
                    <div class="kpi-val">{{ $summary['completed_count'] ?? 0 }} / {{ $summary['rejected_count'] ?? 0 }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Total Refund</div>
                    <div class="kpi-val">Rp {{ number_format($summary['total_refund'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Tgl Klaim</th>
                        <th>Tiket Asal</th>
                        <th>Tiket RMA</th>
                        <th>Pelanggan</th>
                        <th>Komponen / Jasa</th>
                        <th>Keluhan Masalah</th>
                        <th>Status</th>
                        <th class="text-right">Refund (Rp)</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['rows'] ?? [] as $w)
                        <tr>
                            <td>{{ $w['claim_date'] }}</td>
                            <td class="font-bold">{{ $w['original_ticket'] }}</td>
                            <td class="font-bold">{{ $w['new_ticket'] }}</td>
                            <td>{{ $w['client_name'] }}</td>
                            <td>{{ $w['item_name'] }}</td>
                            <td>{{ $w['problems'] }}</td>
                            <td>{{ $w['status_label'] }}</td>
                            <td class="text-right font-bold">Rp {{ number_format($w['refund_amount'], 0, ',', '.') }}</td>
                            <td>{{ $w['handler_name'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">Tidak ada klaim garansi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        {{-- Kolom Tanda Tangan Pengesahan --}}
        <div class="footer-signatures">
            <div class="signature-box">
                <div>Dibuat Oleh,</div>
                <div class="signature-line"></div>
                <div>Kepala Teknisi / CS</div>
            </div>
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div class="signature-line"></div>
                <div>Manager / Pimpinan</div>
            </div>
        </div>
    </div>
</body>
</html>
