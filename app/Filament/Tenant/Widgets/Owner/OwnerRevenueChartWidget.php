<?php

namespace App\Filament\Tenant\Widgets\Owner;

use App\Models\Tenant\RepairPayment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================================================
 * WIDGET: GRAFIK TREN PENDAPATAN SERVIS (LINE CHART)
 * ============================================================================
 * Menampilkan visualisasi tren pendapatan omset servis dari kas masuk (RepairPayment).
 * Dilengkapi dengan filter interaktif:
 * - 7 Hari Terakhir (Harian)
 * - 30 Hari Terakhir (Harian)
 * - 12 Bulan Terakhir (Bulanan - Default)
 */
class OwnerRevenueChartWidget extends ChartWidget
{
    // Filter default saat halaman pertama kali dimuat
    public ?string $filter = 'year';

    // Judul kartu widget grafik
    protected ?string $heading = 'Tren Pendapatan Servis';

    // Urutan posisi widget
    protected static ?int $sort = 3;

    // Lebar kolom responsif (8 dari 12 kolom pada desktop agar berdampingan dengan donut chart)
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'lg' => 8,
    ];

    /**
     * Hak Akses:
     * Hanya role 'owner' atau bypass role yang diizinkan melihat grafik pendapatan ini.
     */
    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(settingTenant('tenant_bypass_roles', ['owner']));
    }

    /**
     * Pilihan filter rentang waktu pada dropdown kanan atas grafik.
     */
    protected function getFilters(): ?array
    {
        return [
            'week' => '7 Hari Terakhir',
            'month' => '30 Hari Terakhir',
            'year' => '12 Bulan Terakhir',
        ];
    }

    /**
     * Pengambilan data dan penyusunan dataset untuk Chart.js.
     */
    protected function getData(): array
    {
        // ---------------------------------------------------------------------
        // 1. Filter Cabang Aktif
        // ---------------------------------------------------------------------
        $branchId = session('current_branch_id');
        $activeFilter = $this->filter;

        $baseQuery = RepairPayment::query();
        if ($branchId) {
            $baseQuery->whereHas('repairInvoice.repair', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $data = [];
        $labels = [];

        // ---------------------------------------------------------------------
        // 2. Pemrosesan Data Berdasarkan Filter Waktu
        // ---------------------------------------------------------------------
        if ($activeFilter === 'week') {
            // Rentang 7 hari terakhir
            for ($i = 6; $i >= 0; $i--) {
                $day = Carbon::now()->subDays($i);
                $labels[] = $day->translatedFormat('d M');
                $data[] = (clone $baseQuery)
                    ->whereDate('payment_date', $day)
                    ->sum('amount');
            }
        } elseif ($activeFilter === 'month') {
            // Rentang 30 hari terakhir
            for ($i = 29; $i >= 0; $i--) {
                $day = Carbon::now()->subDays($i);
                $labels[] = $day->translatedFormat('d M');
                $data[] = (clone $baseQuery)
                    ->whereDate('payment_date', $day)
                    ->sum('amount');
            }
        } else {
            // Default: 12 bulan terakhir
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $labels[] = $month->translatedFormat('M Y');
                $data[] = (clone $baseQuery)
                    ->whereYear('payment_date', $month->year)
                    ->whereMonth('payment_date', $month->month)
                    ->sum('amount');
            }
        }

        // ---------------------------------------------------------------------
        // 3. Konfigurasi Tampilan Kurva (Garis Hijau Emerald dengan Area Fill)
        // ---------------------------------------------------------------------
        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $data,
                    'fill' => 'start',
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Tipe visualisasi Chart.js: Garis (line chart).
     */
    protected function getType(): string
    {
        return 'line';
    }
}
