<?php

namespace App\Filament\Tenant\Pages\Dashboard;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================================================
 * HALAMAN: DASHBOARD OWNER
 * ============================================================================
 * Rute: /admin/owner
 * Hak Akses: Khusus role 'owner' atau bypass role tenant.
 * 
 * Halaman ini merangkum seluruh metrik bisnis utama:
 * 1. Header Widgets: Kartu ucapan selamat datang & profil akun
 * 2. Operasional Harian: DailyOperationsWidget (servis masuk, proses, siap diambil)
 * 3. Ringkasan Finansial: OwnerFinancialOverviewWidget (omset, piutang, klien)
 * 4. Analitik Grafik:
 *    - OwnerRevenueChartWidget (Tren pendapatan kas riil 12 bulan / 30 hari / 7 hari)
 *    - OwnerRepairStatusChartWidget (Donut komposisi status servis aktif)
 * 5. Tabel Aktivitas: OwnerRecentRepairsWidget (5 servis masuk terbaru)
 */
class OwnerDashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static string $routePath = 'owner';
    protected static ?string $title = 'Dashboard Owner';
    protected static ?string $navigationLabel = 'Dashboard';

    /**
     * Membatasi akses hanya untuk pengguna dengan role Owner / Super Admin Tenant.
     */
    public static function canAccess(): bool
    {
        return Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['owner']));
    }

    /**
     * Grid Layout 12 Kolom untuk Dashboard.
     * Memungkinkan penataan widget secara fleksibel dan berdampingan di layar besar (lg).
     */
    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'lg' => 12,
        ];
    }

    /**
     * Widget Bagian Header (Paling Atas).
     */
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Tenant\Widgets\WelcomeTenantWidget::class,
            \Filament\Widgets\AccountWidget::class,
        ];
    }

    /**
     * Daftar Widget Utama Dashboard Owner (Area Grid Konten).
     */
    public function getWidgets(): array
    {
        return [
            // Operasional Harian Toko
            \App\Filament\Tenant\Widgets\DailyOperationsWidget::class,

            // KPI Keuangan, Piutang & Pertumbuhan Klien (Subfolder Widgets/Owner)
            \App\Filament\Tenant\Widgets\Owner\OwnerFinancialOverviewWidget::class,

            // Grafik Tren Pendapatan (Span 8 Kolom di desktop)
            \App\Filament\Tenant\Widgets\Owner\OwnerRevenueChartWidget::class,

            // Grafik Donut Komposisi Status Unit (Span 4 Kolom di desktop)
            \App\Filament\Tenant\Widgets\Owner\OwnerRepairStatusChartWidget::class,

            // Tabel 5 Servis Masuk Terbaru (Full Width)
            \App\Filament\Tenant\Widgets\Owner\OwnerRecentRepairsWidget::class,
        ];
    }
}
