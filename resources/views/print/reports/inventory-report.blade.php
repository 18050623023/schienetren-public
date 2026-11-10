<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Laporan Stok & Gudang - 
        {{ match($tab) {
            'movements' => 'Kartu Stok & Mutasi',
            'opnames' => 'Selisih Stock Opname',
            'transfers' => 'Transfer Antar Cabang',
            'purchases' => 'Pengadaan Barang Supplier',
            default => 'Valuasi & Persediaan Stok',
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
        table, th, td {
            border: 1px solid #444;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }
        .signature {
            width: 220px;
        }
        .signature div {
            margin-top: 55px;
            border-bottom: 1px solid #333;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="no-print" style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <button onclick="window.print()" style="padding: 8px 16px; cursor: pointer; background: #2563eb; color: #fff; border: none; border-radius: 4px; font-weight: bold;">
                Cetak Dokumen
            </button>
            <span style="color: #666; font-size: 11px;">Gunakan browser print (Ctrl+P) untuk menyimpan sebagai PDF</span>
        </div>

        <div class="header">
            <h1>
                {{ settingTenant('brand_name', 'SCHIENETREN') }}
            </h1>
            <h2>
                LAPORAN 
                {{ match($tab) {
                    'movements' => 'KARTU STOK & ARUS MUTASI BARANG',
                    'opnames' => 'AUDIT SELISIH STOCK OPNAME',
                    'transfers' => 'MUTASI TRANSFER BARANG ANTAR CABANG',
                    'purchases' => 'PENGADAAN & PEMBELIAN SUPPLIER (PO)',
                    default => 'VALUASI & NILAI ASET PERSEDIAAN GUDANG',
                } }}
            </h2>
        </div>

        <div class="meta-box">
            <div>
                <strong>Cabang:</strong> {{ $branch?->name ?? 'Semua Cabang' }}<br>
                <strong>Produk:</strong> {{ $product?->name ?? 'Semua Produk / Sparepart' }}
            </div>
            <div style="text-align: right;">
                @if ($tab !== 'valuation')
                    <strong>Periode:</strong> {{ \Carbon\Carbon::parse($filters['date_from'] ?? now()->startOfMonth())->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($filters['date_to'] ?? now()->endOfMonth())->format('d/m/Y') }}<br>
                @endif
                <strong>Dicetak Pada:</strong> {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>

        {{-- TAB 1: VALUASI STOK --}}
        @if ($tab === 'valuation')
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 30px;">No</th>
                        <th>SKU</th>
                        <th>Nama Produk & Varian</th>
                        <th>Kategori / Brand</th>
                        <th>Cabang</th>
                        <th class="text-center">Stok</th>
                        <th class="text-right">Harga Modal (HPP)</th>
                        <th class="text-right">Harga Jual</th>
                        <th class="text-right">Total Nilai Modal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['rows'] ?? [] as $index => $r)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $r['sku'] }}</td>
                            <td class="font-bold">{{ $r['product_name'] }}</td>
                            <td>{{ $r['brand'] }} / {{ $r['category'] }}</td>
                            <td>{{ $r['branch'] }}</td>
                            <td class="text-center font-bold">{{ $r['stock'] }} {{ $r['unit'] }}</td>
                            <td class="text-right">Rp {{ number_format($r['purchase_price'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($r['sale_price'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold">Rp {{ number_format($r['total_cost'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data persediaan stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        {{-- TAB 2: MUTASI STOK --}}
        @if ($tab === 'movements')
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 30px;">No</th>
                        <th>Waktu Transaksi</th>
                        <th>Produk & Varian</th>
                        <th>Cabang</th>
                        <th class="text-center">Arah</th>
                        <th class="text-center">Jumlah</th>
                        <th>Sumber</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['movements'] ?? [] as $index => $m)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $m->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="font-bold">{{ $m->product?->name ?? 'Produk' }} {{ $m->productVariant?->name ?? '' }}</td>
                            <td>{{ $m->branch?->name ?? '-' }}</td>
                            <td class="text-center font-bold">{{ strtoupper($m->direction) }}</td>
                            <td class="text-center font-bold">{{ $m->direction === 'in' ? '+' : '-' }}{{ $m->quantity }}</td>
                            <td>{{ class_basename($m->source_type ?? 'Manual') }}</td>
                            <td>{{ $m->note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada mutasi barang pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        {{-- TAB 3: STOCK OPNAME --}}
        @if ($tab === 'opnames')
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Referensi</th>
                        <th>Cabang</th>
                        <th>Produk</th>
                        <th class="text-center">Sistem</th>
                        <th class="text-center">Fisik</th>
                        <th class="text-center">Selisih</th>
                        <th class="text-right">Nilai Selisih</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasOp = false; @endphp
                    @foreach ($reportData['opnames'] ?? [] as $op)
                        @foreach ($op->items as $item)
                            @php
                                $hasOp = true;
                                $diff = (int) $item->difference;
                                $cost = (float) ($item->productVariant?->purchase_price ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $op->date?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $op->reference }}</td>
                                <td>{{ $op->branch?->name ?? '-' }}</td>
                                <td class="font-bold">{{ $item->product?->name ?? '-' }} {{ $item->productVariant?->name ?? '' }}</td>
                                <td class="text-center">{{ $item->system_qty }}</td>
                                <td class="text-center font-bold">{{ $item->actual_qty }}</td>
                                <td class="text-center font-bold">{{ $diff > 0 ? '+' : '' }}{{ $diff }}</td>
                                <td class="text-right font-bold">Rp {{ number_format(abs($diff * $cost), 0, ',', '.') }}</td>
                                <td class="text-center">{{ strtoupper($op->status) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    @if (!$hasOp)
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada audit stock opname pada periode ini.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @endif

        {{-- TAB 4: TRANSFER ANTAR CABANG --}}
        @if ($tab === 'transfers')
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 30px;">No</th>
                        <th>Kode Mutasi</th>
                        <th>Tanggal</th>
                        <th>Cabang Pengirim</th>
                        <th>Cabang Penerima</th>
                        <th class="text-center">Total Unit</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['transfers'] ?? [] as $index => $t)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $t->code }}</td>
                            <td>{{ \Carbon\Carbon::parse($t->date)->format('d/m/Y') }}</td>
                            <td>{{ $t->fromBranch?->name ?? '-' }}</td>
                            <td class="font-bold">{{ $t->toBranch?->name ?? '-' }}</td>
                            <td class="text-center font-bold">{{ $t->items->sum('quantity') }} Qty</td>
                            <td class="text-center font-bold">{{ strtoupper($t->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada transfer barang antar cabang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        {{-- TAB 5: PENGADAAN & PO --}}
        @if ($tab === 'purchases')
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 30px;">No</th>
                        <th>Kode PO</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Cabang</th>
                        <th class="text-right">Total Belanja</th>
                        <th class="text-right">Sudah Dibayar</th>
                        <th class="text-right">Sisa Hutang</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['purchases'] ?? [] as $index => $p)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $p->code }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->date)->format('d/m/Y') }}</td>
                            <td class="font-bold">{{ $p->supplier?->name ?? '-' }}</td>
                            <td>{{ $p->branch?->name ?? '-' }}</td>
                            <td class="text-right font-bold">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($p->paid_amount, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($p->remaining_amount, 0, ',', '.') }}</td>
                            <td class="text-center font-bold">{{ strtoupper($p->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data pembelian supplier pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        <div class="footer">
            <div class="signature">
                Petugas Gudang / Logistik,
                <div>{{ auth('tenant')->user()?->name ?? '..................................' }}</div>
            </div>
            <div class="signature">
                Mengetahui & Menyetujui,<br>
                (Kepala Cabang / Owner)
                <div>..................................</div>
            </div>
        </div>
    </div>
</body>
</html>
