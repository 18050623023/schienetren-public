<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TenantFeature: string implements HasLabel
{
    // Limitasi Kuota (Type: limit)
    case TENANT = 'tenant';
    case MAX_USER = 'max_user';
    case MAX_BRANCH = 'max_branch';
    case MAX_PRODUCT = 'max_product';
    case MAX_MONTHLY_REPAIR = 'max_monthly_repair';

    // Operasional Servis & Pelanggan (Type: boolean)
    case KANBAN_TECHNICIAN = 'kanban_technician';

    // Operasional Toko & Inventori (Type: boolean)
    case POS = 'pos';
    case PURCHASE_ORDER = 'purchase_order';
    case STOCK_TRANSFER = 'stock_transfer';
    case STOCK_OPNAME = 'stock_opname';

    // Keuangan & Akuntansi (Type: boolean)
    case FINANCE_ADVANCE = 'finance_advance';

    // SDM & Payroll (Type: boolean)
    case LEAVE = 'leave';
    case HOLIDAY = 'holiday';
    case SHIFT_MANAGEMENT = 'shift_management';
    case PAYROLL = 'payroll';
    case ATTENDANCE_GEOFENCE = 'attendance_geofence';
    case SKILL_MANAGEMENT = 'skill_management';

    // Laporan & Analitik (Type: boolean)
    case ADVANCED_REPORTS = 'advanced_reports';

    public function getLabel(): ?string
    {
        static $labels = null;
        if ($labels === null) {
            try {
                $labels = \App\Models\Feature::pluck('name', 'code')->toArray();
            } catch (\Throwable $e) {
                $labels = [];
            }
        }

        return $labels[$this->value] ?? match ($this) {
            self::TENANT => 'Maksimal Bisnis Tenant',
            self::MAX_USER => 'Maksimal Pengguna',
            self::MAX_BRANCH => 'Maksimal Cabang',
            self::MAX_PRODUCT => 'Maksimal Katalog Produk',
            self::MAX_MONTHLY_REPAIR => 'Maksimal Servis per Bulan',
            self::KANBAN_TECHNICIAN => 'Papan Kanban Teknisi',
            self::POS => 'Kasir Penjualan Ritel (POS)',
            self::PURCHASE_ORDER => 'Purchase Order & Pengadaan',
            self::STOCK_TRANSFER => 'Mutasi Stok Antar Cabang',
            self::STOCK_OPNAME => 'Audit & Penyesuaian Stok (Opname)',
            self::FINANCE_ADVANCE => 'Keuangan Lanjutan (Double-Entry & Jurnal)',
            self::LEAVE => 'Manajemen Cuti',
            self::HOLIDAY => 'Hari Libur Nasional',
            self::SHIFT_MANAGEMENT => 'Manajemen Shift Kerja',
            self::PAYROLL => 'Penggajian & Kompensasi (Payroll)',
            self::ATTENDANCE_GEOFENCE => 'Absensi Geofencing (GPS)',
            self::SKILL_MANAGEMENT => 'Manajemen Skill Teknisi',
            self::ADVANCED_REPORTS => 'Laporan Lanjutan & Ekspor',
        };
    }

    public function getType(): string
    {
        static $types = null;
        if ($types === null) {
            try {
                $types = \App\Models\Feature::pluck('type', 'code')->toArray();
            } catch (\Throwable $e) {
                $types = [];
            }
        }

        return $types[$this->value] ?? match ($this) {
            self::TENANT,
            self::MAX_USER,
            self::MAX_BRANCH,
            self::MAX_PRODUCT,
            self::MAX_MONTHLY_REPAIR => 'limit',
            default => 'boolean',
        };
    }

    public function getDefaultValue(): mixed
    {
        return match ($this->getType()) {
            'limit', 'number' => 0,
            'boolean' => false,
            default => null,
        };
    }

    /**
     * Get default features with their respective default values
     */
    public static function getDefaults(): array
    {
        $defaults = [];
        foreach (self::cases() as $case) {
            $defaults[$case->value] = $case->getDefaultValue();
        }
        return $defaults;
    }
}
