<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Laporan HR - 
        {{ match($tab) {
            'leave' => 'Cuti dan Izin',
            'payroll' => 'Penggajian',
            default => 'Presensi dan Jam Kerja',
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
                    'leave' => 'REKAPITULASI CUTI & IZIN KARYAWAN',
                    'payroll' => 'REKAPITULASI PENGGAJIAN & KOMISI',
                    default => 'REKAPITULASI PRESENSI & JAM KERJA',
                } }}
            </h2>
        </div>

        <div class="meta-box">
            <div>
                <strong>Cabang:</strong> {{ $branch?->name ?? 'Semua Cabang' }}<br>
                <strong>Karyawan:</strong> {{ $employee?->user?->name ?? 'Semua Karyawan' }}
            </div>
            <div style="text-align: right;">
                @if ($tab === 'payroll')
                    <strong>Periode Gaji:</strong> {{ $filters['period'] ?? date('Y-m') }}<br>
                @else
                    <strong>Periode:</strong> {{ \Carbon\Carbon::parse($filters['date_from'] ?? now()->startOfMonth())->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($filters['date_to'] ?? now()->endOfMonth())->format('d/m/Y') }}<br>
                @endif
                <strong>Dicetak Pada:</strong> {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>

        {{-- TAB 1: TABEL PRESENSI --}}
        @if ($tab === 'attendance')
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 30px;">No</th>
                        <th>Nama Karyawan</th>
                        <th>Kode</th>
                        <th>Cabang</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Tepat Waktu</th>
                        <th class="text-center">Terlambat</th>
                        <th class="text-center">Pulang Cepat</th>
                        <th class="text-center">Lembur</th>
                        <th class="text-center">Cuti/Izin</th>
                        <th class="text-center">Alpha</th>
                        <th class="text-right">% Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['rows'] ?? [] as $index => $row)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $row['name'] }}</td>
                            <td>{{ $row['code_pegawai'] }}</td>
                            <td>{{ $row['branch_name'] }}</td>
                            <td class="text-center">{{ $row['present_days'] + $row['late_days'] }} hr</td>
                            <td class="text-center">{{ $row['present_days'] }} hr</td>
                            <td class="text-center">
                                {{ $row['late_days'] }} hr
                                @if ($row['late_minutes'] > 0)
                                    ({{ $row['late_minutes'] }}m)
                                @endif
                            </td>
                            <td class="text-center">{{ $row['early_minutes'] > 0 ? $row['early_minutes'] . 'm' : '-' }}</td>
                            <td class="text-center">{{ $row['overtime_minutes'] > 0 ? number_format($row['overtime_minutes'] / 60, 1) . ' jam' : '-' }}</td>
                            <td class="text-center">{{ $row['leave_days'] > 0 ? $row['leave_days'] . ' hr' : '-' }}</td>
                            <td class="text-center">{{ $row['absent_days'] > 0 ? $row['absent_days'] . ' hr' : '-' }}</td>
                            <td class="text-right font-bold">{{ $row['attendance_rate'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center">Tidak ada data presensi pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        {{-- TAB 2: TABEL CUTI & IZIN --}}
        @if ($tab === 'leave')
            <h3 style="font-size: 12px; margin: 10px 0 6px;">Riwayat Pengajuan Cuti Periode Ini</h3>
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 30px;">No</th>
                        <th>Nama Karyawan</th>
                        <th>Jenis Cuti</th>
                        <th>Rentang Tanggal</th>
                        <th class="text-center">Durasi</th>
                        <th>Alasan</th>
                        <th class="text-center">Status</th>
                        <th>Disetujui/Ditolak</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['requests'] ?? [] as $index => $req)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $req->employee?->user?->name ?? 'Karyawan #' . $req->employee_id }}</td>
                            <td>{{ $req->leaveType?->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($req->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d/m/Y') }}</td>
                            <td class="text-center font-bold">{{ $req->total_days }} hari</td>
                            <td>{{ $req->reason }}</td>
                            <td class="text-center font-bold">{{ strtoupper($req->status) }}</td>
                            <td>{{ $req->approvedBy?->name ?? $req->rejectedBy?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data cuti pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <h3 style="font-size: 12px; margin: 20px 0 6px;">Sisa Kuota Cuti Karyawan</h3>
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 30px;">No</th>
                        <th>Nama Karyawan</th>
                        <th>Jenis Cuti</th>
                        <th class="text-center">Kuota Awal</th>
                        <th class="text-center">Terpakai</th>
                        <th class="text-center">Sisa Saldo</th>
                        <th>Periode Kuota</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['balances'] ?? [] as $index => $b)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $b->employee?->user?->name ?? 'Karyawan #' . $b->employee_id }}</td>
                            <td>{{ $b->leaveType?->name ?? '-' }}</td>
                            <td class="text-center">{{ $b->quota }} hari</td>
                            <td class="text-center">{{ $b->used }} hari</td>
                            <td class="text-center font-bold">{{ $b->remaining }} hari</td>
                            <td>{{ \Carbon\Carbon::parse($b->period_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($b->period_end)->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada catatan kuota cuti.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        {{-- TAB 3: TABEL PENGGAJIAN --}}
        @if ($tab === 'payroll')
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 30px;">No</th>
                        <th>Periode</th>
                        <th>Nama Karyawan</th>
                        <th>Cabang</th>
                        <th class="text-right">Gaji Pokok</th>
                        <th class="text-right">Tunjangan & Komisi</th>
                        <th class="text-right">Potongan</th>
                        <th class="text-right">Gaji Bersih</th>
                        <th class="text-center">Metode Bayar</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['payrolls'] ?? [] as $index => $p)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $p->period }}</td>
                            <td class="font-bold">{{ $p->employee?->user?->name ?? 'Karyawan #' . $p->employee_id }}</td>
                            <td>{{ $p->branch?->name ?? '-' }}</td>
                            <td class="text-right">Rp {{ number_format($p->basic_salary, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($p->total_allowance, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($p->total_deduction, 0, ',', '.') }}</td>
                            <td class="text-right font-bold">Rp {{ number_format($p->net_salary, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $p->paymentMethod?->name ?? '-' }}</td>
                            <td class="text-center font-bold">{{ strtoupper($p->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Tidak ada slip gaji pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        <div class="footer">
            <div class="signature">
                Dibuat Oleh,<br>
                (Bagian HR / Admin)
                <div>{{ auth('tenant')->user()?->name ?? '..................................' }}</div>
            </div>
            <div class="signature">
                Mengetahui & Menyetujui,<br>
                (Pimpinan Cabang / Owner)
                <div>..................................</div>
            </div>
        </div>
    </div>
</body>
</html>
