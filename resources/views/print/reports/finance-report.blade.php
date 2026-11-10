<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Laporan Keuangan - 
        {{ match($tab) {
            'profit_loss' => 'Laba Rugi Operasional',
            'receivables' => 'Buku Piutang Pelanggan',
            'payables' => 'Buku Hutang Supplier',
            'expenses' => 'Beban Operasional Toko',
            default => 'Arus Kas & Buku Kasir',
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
            font-size: 13px;
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
            <h1>{{ tenant('name') ?? 'SCHIENETREN FINANCE & ERP' }}</h1>
            <h2>
                LAPORAN 
                {{ match($tab) {
                    'profit_loss' => 'LABA RUGI OPERASIONAL (SIMPLE P&L)',
                    'receivables' => 'BUKU PIUTANG PELANGGAN',
                    'payables' => 'BUKU HUTANG SUPPLIER (PO)',
                    'expenses' => 'BEBAN OPERASIONAL TOKO',
                    default => 'ARUS KAS MASUK & KELUAR',
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
                @if ($paymentMethod)
                    <strong>Metode Bayar:</strong> {{ $paymentMethod->name }}<br>
                @endif
                <strong>Dicetak Pada:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
            </div>
        </div>

        @php
            $summary = $reportData['summary'] ?? [];
        @endphp

        {{-- TAB 1: ARUS KAS --}}
        @if ($tab === 'cashflow')
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-title">Total Kas Masuk</div>
                    <div class="kpi-val" style="color: #16a34a;">Rp {{ number_format($summary['total_cash_in'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Total Kas Keluar</div>
                    <div class="kpi-val" style="color: #dc2626;">Rp {{ number_format($summary['total_cash_out'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Arus Kas Bersih (Net)</div>
                    <div class="kpi-val" style="color: #2563eb;">Rp {{ number_format($summary['net_cash_flow'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Total Transaksi</div>
                    <div class="kpi-val">{{ number_format($summary['total_transactions'] ?? 0) }}</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Aliran</th>
                        <th>No Referensi</th>
                        <th>Keterangan Transaksi</th>
                        <th>Cabang</th>
                        <th>Metode Bayar</th>
                        <th class="text-right">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['rows'] ?? [] as $r)
                        <tr>
                            <td>{{ $r['date'] }}</td>
                            <td class="font-bold {{ $r['type'] === 'in' ? 'color: #16a34a;' : 'color: #dc2626;' }}">{{ $r['type_label'] }}</td>
                            <td class="font-bold">{{ $r['reference'] }}</td>
                            <td>{{ $r['description'] }}</td>
                            <td>{{ $r['branch'] }}</td>
                            <td>{{ $r['method'] }}</td>
                            <td class="text-right font-bold {{ $r['type'] === 'in' ? 'color: #16a34a;' : 'color: #dc2626;' }}">
                                {{ $r['type'] === 'in' ? '+' : '-' }}Rp {{ number_format($r['amount'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Tidak ada transaksi kas.</td></tr>
                    @endforelse
                </tbody>
            </table>

        {{-- TAB 2: LABA RUGI --}}
        @elseif ($tab === 'profit_loss')
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-title">Total Omset</div>
                    <div class="kpi-val">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Modal HPP Sparepart</div>
                    <div class="kpi-val" style="color: #d97706;">Rp {{ number_format($summary['total_cogs'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Beban Operasional Toko</div>
                    <div class="kpi-val" style="color: #dc2626;">Rp {{ number_format($summary['total_operating_expenses'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Laba Bersih Operasional</div>
                    <div class="kpi-val" style="color: #16a34a;">Rp {{ number_format($summary['net_profit'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Pos Keuangan</th>
                        <th class="text-right">Nominal (Rp)</th>
                        <th>Keterangan / Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-bold">Total Pendapatan Operasional (Faktur)</td>
                        <td class="text-right font-bold">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</td>
                        <td>100.0% dari total omset</td>
                    </tr>
                    <tr>
                        <td>(-) Harga Pokok Penjualan (HPP Sparepart Terjual)</td>
                        <td class="text-right" style="color: #dc2626;">-Rp {{ number_format($summary['total_cogs'] ?? 0, 0, ',', '.') }}</td>
                        <td>Biaya modal suku cadang yang digunakan</td>
                    </tr>
                    <tr style="background: #f5f5f5;">
                        <td class="font-bold">(=) Laba Kotor (Gross Profit)</td>
                        <td class="text-right font-bold" style="color: #2563eb;">Rp {{ number_format($summary['gross_profit'] ?? 0, 0, ',', '.') }}</td>
                        <td>Margin Kotor: {{ $summary['gross_margin_pct'] ?? 0 }}%</td>
                    </tr>
                    <tr>
                        <td>(-) Total Beban Biaya Operasional Toko</td>
                        <td class="text-right" style="color: #dc2626;">-Rp {{ number_format($summary['total_operating_expenses'] ?? 0, 0, ',', '.') }}</td>
                        <td>Total pengeluaran operasional toko</td>
                    </tr>
                    <tr style="background: #eef2ff;">
                        <td class="font-bold">(=) Laba Bersih Operasional (Net Profit)</td>
                        <td class="text-right font-bold" style="color: #16a34a; font-size: 11px;">Rp {{ number_format($summary['net_profit'] ?? 0, 0, ',', '.') }}</td>
                        <td>Margin Bersih: {{ $summary['net_margin_pct'] ?? 0 }}%</td>
                    </tr>
                </tbody>
            </table>

            <h3 style="font-size: 11px; margin-top: 18px; margin-bottom: 6px;">Rincian Beban Biaya Operasional Toko</h3>
            <table>
                <thead>
                    <tr>
                        <th>Kategori Beban</th>
                        <th class="text-right">Nominal (Rp)</th>
                        <th class="text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['expense_breakdown'] ?? [] as $eb)
                        <tr>
                            <td class="font-bold">{{ $eb['name'] }}</td>
                            <td class="text-right">Rp {{ number_format($eb['amount'], 0, ',', '.') }}</td>
                            <td class="text-center">{{ $eb['percentage'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center">Tidak ada rincian beban.</td></tr>
                    @endforelse
                </tbody>
            </table>

        {{-- TAB 3: PIUTANG --}}
        @elseif ($tab === 'receivables')
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-title">Sisa Piutang Tertahan</div>
                    <div class="kpi-val" style="color: #dc2626;">Rp {{ number_format($summary['total_receivables'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Piutang Overdue</div>
                    <div class="kpi-val" style="color: #d97706;">Rp {{ number_format($summary['overdue_amount'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Recovery Rate</div>
                    <div class="kpi-val" style="color: #16a34a;">{{ $summary['recovery_rate'] ?? 0 }}%</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Invoice Belum Lunas</div>
                    <div class="kpi-val">{{ number_format($summary['total_invoices_count'] ?? 0) }}</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No Invoice</th>
                        <th>Tiket Servis</th>
                        <th>Pelanggan</th>
                        <th>Telepon/WA</th>
                        <th>Cabang</th>
                        <th>Tgl Invoice</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-right">Total Tagihan</th>
                        <th class="text-right">Sudah Dibayar</th>
                        <th class="text-right">Sisa Piutang (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['rows'] ?? [] as $r)
                        <tr>
                            <td class="font-bold">{{ $r['invoice_number'] }}</td>
                            <td>{{ $r['repair_code'] }}</td>
                            <td class="font-bold">{{ $r['client_name'] }}</td>
                            <td>{{ $r['client_whatsapp'] !== '-' ? $r['client_whatsapp'] : $r['client_phone'] }}</td>
                            <td>{{ $r['branch_name'] }}</td>
                            <td>{{ $r['invoice_date'] }}</td>
                            <td>{{ $r['due_date'] }} {{ $r['is_overdue'] ? '(Overdue)' : '' }}</td>
                            <td class="text-right">Rp {{ number_format($r['grand_total'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($r['paid_amount'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold" style="color: #dc2626;">Rp {{ number_format($r['remaining_amount'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center">Tidak ada piutang pelanggan.</td></tr>
                    @endforelse
                </tbody>
            </table>

        {{-- TAB 4: HUTANG SUPPLIER --}}
        @elseif ($tab === 'payables')
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-title">Total Sisa Hutang Toko</div>
                    <div class="kpi-val" style="color: #dc2626;">Rp {{ number_format($summary['total_payables'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Total Pembelian PO</div>
                    <div class="kpi-val">Rp {{ number_format($summary['total_po_value'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Sudah Dibayar Toko</div>
                    <div class="kpi-val" style="color: #16a34a;">Rp {{ number_format($summary['total_paid_to_suppliers'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">PO Belum Lunas</div>
                    <div class="kpi-val">{{ number_format($summary['total_pos_count'] ?? 0) }}</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Kode PO</th>
                        <th>Tanggal PO</th>
                        <th>Nama Supplier</th>
                        <th>Kontak</th>
                        <th>Cabang</th>
                        <th class="text-right">Total Pembelian</th>
                        <th class="text-right">Sudah Dibayar</th>
                        <th class="text-right">Sisa Hutang (Rp)</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['rows'] ?? [] as $p)
                        <tr>
                            <td class="font-bold">{{ $p['code'] }}</td>
                            <td>{{ $p['date'] }}</td>
                            <td class="font-bold">{{ $p['supplier_name'] }}</td>
                            <td>{{ $p['supplier_phone'] }}</td>
                            <td>{{ $p['branch_name'] }}</td>
                            <td class="text-right">Rp {{ number_format($p['total'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($p['paid_amount'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold" style="color: #dc2626;">Rp {{ number_format($p['remaining_amount'], 0, ',', '.') }}</td>
                            <td class="text-center font-bold">{{ $p['status'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">Tidak ada hutang supplier.</td></tr>
                    @endforelse
                </tbody>
            </table>

        {{-- TAB 5: BIAYA OPERASIONAL --}}
        @elseif ($tab === 'expenses')
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-title">Total Beban Toko</div>
                    <div class="kpi-val" style="color: #dc2626;">Rp {{ number_format($summary['total_expenses'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Kategori Terbesar</div>
                    <div class="kpi-val">{{ $summary['top_category_name'] ?? '-' }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Jumlah Kategori</div>
                    <div class="kpi-val">{{ count($reportData['categories'] ?? []) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Total Nota Biaya</div>
                    <div class="kpi-val">{{ number_format($summary['total_transactions'] ?? 0) }}</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No Referensi</th>
                        <th>Cabang</th>
                        <th>Kategori Beban</th>
                        <th>Metode Bayar</th>
                        <th>Keterangan</th>
                        <th class="text-right">Nominal (Rp)</th>
                        <th>Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['rows'] ?? [] as $e)
                        <tr>
                            <td>{{ $e['date'] }}</td>
                            <td class="font-bold">{{ $e['reference_number'] }}</td>
                            <td>{{ $e['branch_name'] }}</td>
                            <td class="font-bold">{{ $e['category_name'] }}</td>
                            <td>{{ $e['payment_method_name'] }}</td>
                            <td>{{ $e['description'] }}</td>
                            <td class="text-right font-bold" style="color: #dc2626;">Rp {{ number_format($e['amount'], 0, ',', '.') }}</td>
                            <td>{{ $e['created_by'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">Tidak ada catatan beban pengeluaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        {{-- Kolom Tanda Tangan Pengesahan --}}
        <div class="footer-signatures">
            <div class="signature-box">
                <div>Dibuat Oleh,</div>
                <div class="signature-line"></div>
                <div>Staff / Bagian Keuangan</div>
            </div>
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div class="signature-line"></div>
                <div>Pimpinan Toko / Owner</div>
            </div>
        </div>
    </div>
</body>
</html>
