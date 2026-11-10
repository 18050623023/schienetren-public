<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pengambilan - {{ $repair->code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .col {
            width: 48%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #333;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }
        .signature {
            width: 200px;
        }
        .signature div {
            margin-top: 60px;
            border-bottom: 1px solid #333;
        }
        .notes {
            font-size: 11px;
            margin-top: 20px;
            border: 1px dashed #333;
            padding: 10px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="no-print" style="margin-bottom: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Nota</button>
        </div>

        <div class="header">
            <h1>NOTA PENGAMBILAN & GARANSI</h1>
            <p>{{ $repair->branch?->name ?? settingTenant('brand_name', 'Cabang Utama') }}</p>
        </div>

        <div class="row">
            <div class="col">
                <strong>Data Klien:</strong><br>
                Nama: {{ $repair->client?->name }}<br>
                Telepon/WA: {{ $repair->client?->phone ?? $repair->client?->whatsapp }}<br>
                Alamat: {{ $repair->client?->address ?? '-' }}
            </div>
            <div class="col">
                <strong>Data Reparasi:</strong><br>
                Kode: <strong>{{ $repair->code }}</strong><br>
                Tanggal Diterima: {{ $repair->created_at->format('d/m/Y H:i') }}<br>
                Diterima Oleh: {{ $repair->receivedBy?->name ?? '-' }}<br>
                Status: {{ $repair->status->getLabel() ?? $repair->status }}
            </div>
        </div>

        <strong>Detail Perangkat:</strong>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Perangkat</th>
                    <th>Serial Number</th>
                    <th>Kelengkapan</th>
                    <th>Keluhan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($repair->repairDevices as $index => $device)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $device->deviceType?->name ?? 'Perangkat' }}</td>
                    <td>{{ $device->serial_number ?? '-' }}</td>
                    <td>{{ $device->accessories ?? '-' }}</td>
                    <td>{{ $device->complaint ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($repair->notes)
        <div style="margin-bottom: 20px;">
            <strong>Catatan Tambahan:</strong><br>
            {{ $repair->notes }}
        </div>
        @endif

        <div class="notes">
            <strong>SYARAT DAN KETENTUAN (GARANSI & PENGAMBILAN):</strong>
            <div style="margin-top: 5px; line-height: 1.4; white-space: pre-line;">{{ settingTenant('repair_terms_and_conditions', "1. Nota ini adalah bukti sah pengambilan perangkat. Harap dibawa saat mengambil perangkat.\n2. Garansi berlaku sesuai dengan kesepakatan jenis perbaikan dan tidak berlaku untuk kerusakan akibat kelalaian pengguna (jatuh, kena air, dll).\n3. Perangkat yang tidak diambil lebih dari " . settingTenant('repair_uncollected_days_limit', 30) . " hari sejak pemberitahuan selesai, di luar tanggung jawab kami.\n4. Segel garansi yang rusak atau robek akan membatalkan garansi.") }}</div>
        </div>

        <div class="footer">
            <div class="signature">
                Hormat Kami,<br>
                (Penerima/Teknisi)
                <div>{{ $repair->receivedBy?->name ?? '.......................' }}</div>
            </div>
            <div class="signature">
                Klien,<br>
                (Pemilik Perangkat)
                <div>{{ $repair->client?->name ?? '.......................' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
