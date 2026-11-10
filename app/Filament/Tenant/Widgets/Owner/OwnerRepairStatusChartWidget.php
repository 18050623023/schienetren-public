<?php

namespace App\Filament\Tenant\Widgets\Owner;

use App\Enums\RepairStatus;
use App\Models\Tenant\Repair;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================================================
 * WIDGET: DISTRIBUSI STATUS SERVIS (DOUGHNUT CHART)
 * ============================================================================
 * Menampilkan diagram donat proporsi status unit perbaikan di toko.
 * Membantu Owner memonitor bottleneck operasional secara visual:
 * - Unit masuk, diagnosa, antrean suku cadang, pengerjaan, QC, dan siap diambil.
 */
class OwnerRepairStatusChartWidget extends ChartWidget
{
    // Filter default: Hanya unit yang berstatus aktif (sedang dalam perbaikan)
    public ?string $filter = 'active';

    // Judul kartu widget grafik
    protected ?string $heading = 'Distribusi Status Servis';

    // Urutan posisi widget
    protected static ?int $sort = 4;

    // Lebar kolom responsif (4 dari 12 kolom pada desktop agar berdampingan dengan revenue chart)
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'lg' => 4,
    ];

    /**
     * Hak Akses:
     * Hanya role 'owner' atau bypass role yang diizinkan melihat grafik status ini.
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
     * Pilihan filter status: Unit aktif berjalan vs Semua riwayat.
     */
    protected function getFilters(): ?array
    {
        return [
            'active' => 'Servis Aktif',
            'all' => 'Semua Status',
        ];
    }

    /**
     * Pengambilan data agregasi status dan penyusunan palet warna Donut.
     */
    protected function getData(): array
    {
        // ---------------------------------------------------------------------
        // 1. Filter Cabang Aktif
        // ---------------------------------------------------------------------
        $branchId = session('current_branch_id');
        $query = Repair::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // ---------------------------------------------------------------------
        // 2. Filter Servis Aktif (Mengecualikan unit yang sudah diambil atau cancel)
        // ---------------------------------------------------------------------
        if ($this->filter === 'active') {
            $query->whereNull('delivered_at')
                ->whereNull('canceled_at')
                ->whereNotIn('status', [RepairStatus::Cancel, RepairStatus::NotPossible, RepairStatus::Delivered]);
        }

        // Agregasi jumlah servis per status
        $repairsByStatus = $query->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $labels = [];
        $data = [];
        $backgroundColors = [];

        // ---------------------------------------------------------------------
        // 3. Pemetaan Warna Harmonis Sesuai Makna Status
        // ---------------------------------------------------------------------
        $colorMap = [
            RepairStatus::Incoming->value => '#3b82f6',             // Biru (Servis Masuk)
            RepairStatus::Checking->value => '#0284c7',             // Biru Langit (Diagnosa)
            RepairStatus::WaitingConfirmation->value => '#f59e0b',  // Kuning Amber (Menunggu Konfirmasi Pelanggan)
            RepairStatus::WaitingSparepart->value => '#ef4444',     // Merah (Menunggu Sparepart)
            RepairStatus::SparepartArrived->value => '#8b5cf6',     // Ungu (Sparepart Tiba)
            RepairStatus::InService->value => '#06b6d4',            // Cyan (Sedang Dikerjakan)
            RepairStatus::RunningTest->value => '#eab308',          // Kuning (QC / Running Test)
            RepairStatus::PartialyDone->value => '#d97706',         // Oranye (Sebagian Selesai)
            RepairStatus::ReadyToPickup->value => '#10b981',        // Hijau Emerald (Bisa Diambil)
            RepairStatus::Delivered->value => '#059669',            // Hijau Tua (Sudah Diambil)
            RepairStatus::TransferTechnician->value => '#64748b',   // Abu Slate (Alih Teknisi)
            RepairStatus::NotPossible->value => '#6b7280',          // Abu Netral (Tidak Bisa)
            RepairStatus::Cancel->value => '#dc2626',               // Merah Tua (Batal)
        ];

        // ---------------------------------------------------------------------
        // 4. Looping Kasus Enum untuk Menyusun Dataset
        // ---------------------------------------------------------------------
        foreach (RepairStatus::cases() as $case) {
            $count = $repairsByStatus[$case->value] ?? 0;
            // Jika filter aktif, lewati status yang nilainya 0 agar grafik tetap bersih
            if ($this->filter === 'active' && $count === 0) {
                continue;
            }

            $labels[] = $case->label();
            $data[] = $count;
            $backgroundColors[] = $colorMap[$case->value] ?? '#94a3b8';
        }

        // Tampilan fallback jika belum ada data servis aktif
        if (empty($data) || array_sum($data) === 0) {
            return [
                'datasets' => [
                    [
                        'label' => 'Unit Servis',
                        'data' => [1],
                        'backgroundColor' => ['#e2e8f0'],
                    ],
                ],
                'labels' => ['Tidak ada servis aktif'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Unit Servis',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Tipe visualisasi Chart.js: Donat (doughnut).
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
}
