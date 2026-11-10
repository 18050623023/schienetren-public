<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembelian - {{ $sale->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
            size: auto;
        }
        body {
            font-family: 'Courier New', Courier, monospace, monospace;
            font-size: 11px;
            line-height: 1.35;
            color: #000;
            margin: 0;
            padding: 8px 12px;
            width: 100%;
            max-width: 80mm;
            box-sizing: border-box;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        
        .header {
            text-align: center;
            margin-bottom: 6px;
        }
        .header .store-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .header .store-info {
            font-size: 10px;
            margin: 1px 0;
            color: #222;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .double-divider {
            border-top: 1px double #000;
            margin: 6px 0;
        }

        .meta-table, .items-table, .total-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            font-size: 10px;
            padding: 1px 0;
            vertical-align: top;
        }
        .items-table th {
            font-size: 10px;
            border-bottom: 1px dashed #000;
            padding: 3px 0;
        }
        .items-table td {
            font-size: 10.5px;
            padding: 2px 0;
            vertical-align: top;
        }
        .item-row {
            margin-bottom: 3px;
        }
        .item-name {
            font-weight: 600;
            word-break: break-word;
        }
        .item-serial {
            font-size: 9.5px;
            font-style: italic;
            color: #333;
            margin-left: 6px;
        }
        .item-warranty {
            font-size: 9.5px;
            color: #444;
            margin-left: 6px;
        }
        
        .total-table td {
            font-size: 10.5px;
            padding: 1.5px 0;
        }
        
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 10px;
        }
        .footer p {
            margin: 2px 0;
        }

        /* Screen only action bar */
        @media screen {
            body {
                margin: 20px auto;
                box-shadow: 0 0 10px rgba(0,0,0,0.15);
                border-radius: 4px;
                padding: 15px;
            }
            .action-bar {
                text-align: center;
                margin-bottom: 15px;
                display: flex;
                gap: 8px;
                justify-content: center;
            }
            .btn-action {
                font-family: sans-serif;
                font-size: 12px;
                padding: 6px 14px;
                background: #f59e0b;
                color: #fff;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
            }
            .btn-close {
                background: #64748b;
            }
        }
        @media print {
            .action-bar { display: none !important; }
            body { box-shadow: none; margin: 0; padding: 4px; }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <button class="btn-action" onclick="window.print()">🖨️ Cetak Struk</button>
        <button class="btn-action btn-close" onclick="window.close()">✖️ Tutup</button>
    </div>

    <div class="header">
        <div class="store-name">{{ $sale->branch?->name ?? settingTenant('brand_name', 'Schienetren Store') }}</div>
        @if($sale->branch?->address)
            <div class="store-info">{{ $sale->branch->address }}</div>
        @endif
        @if($sale->branch?->phone)
            <div class="store-info">Telp: {{ $sale->branch->phone }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <table class="meta-table">
        <tr>
            <td style="width: 45%;">No. Struk</td>
            <td style="width: 5%;">:</td>
            <td class="bold">{{ $sale->invoice_number }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $sale->date ? $sale->date->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td>{{ $sale->cashier?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td>:</td>
            <td>{{ $sale->client?->name ?? 'Walk-in Customer (Umum)' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 50%;">Item</th>
                <th class="text-center" style="width: 15%;">Qty</th>
                <th class="text-right" style="width: 35%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td colspan="3" class="item-name">
                        {{ $item->productVariant?->product?->name ?? 'Produk' }}
                        @if($item->productVariant?->name)
                            ({{ $item->productVariant->name }})
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 9.5px; padding-left: 6px; color: #444;">
                        @ Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        @if($item->discount_amount > 0)
                            <span style="color:#d9534f;">(-{{ number_format($item->discount_amount, 0, ',', '.') }})</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($item->serials->isNotEmpty())
                    <tr>
                        <td colspan="3" class="item-serial">
                            S/N: {{ $item->serials->pluck('serial_number')->join(', ') }}
                        </td>
                    </tr>
                @endif
                @if($item->warranty_days > 0)
                    <tr>
                        <td colspan="3" class="item-warranty">
                            🛡️ Garansi: {{ $item->warranty_days }} Hari
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table class="total-table">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($sale->discount_amount > 0)
            <tr>
                <td>Diskon @if($sale->discount_type === 'percentage')({{ $sale->discount_value }}%)@endif</td>
                <td class="text-right">- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
        @if($sale->tax_amount > 0)
            <tr>
                <td>Pajak</td>
                <td class="text-right">+ Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="bold" style="font-size: 12px;">
            <td style="padding-top: 3px;">TOTAL</td>
            <td class="text-right" style="padding-top: 3px;">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-top: 3px;">Bayar ({{ $sale->paymentMethod?->name ?? 'Tunai' }})</td>
            <td class="text-right" style="padding-top: 3px;">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
        </tr>
        <tr class="bold">
            <td>Kembalian</td>
            <td class="text-right">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="double-divider"></div>

    <div class="footer">
        <p class="bold">{{ settingTenant('pos_receipt_footer_title', 'Terima Kasih Atas Kunjungan Anda!') }}</p>
        <p>{!! nl2br(e(settingTenant('pos_receipt_footer_notes', 'Barang yang sudah dibeli dapat ditukar jika bergaransi resmi sesuai ketentuan toko.'))) !!}</p>
        <p style="font-size: 9px; color: #666; margin-top: 5px;">Powered by {{ settingTenant('brand_name', 'Schienetren') }} POS</p>
    </div>

    <script>
        // Otomatis picu dialog print saat dibuka
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
