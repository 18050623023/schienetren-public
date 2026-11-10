<?php

namespace App\Filament\Tenant\Widgets\Owner;

use App\Filament\Tenant\Resources\Repairs\RepairResource;
use App\Models\Tenant\Repair;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================================================
 * WIDGET: TABEL SERVIS MASUK TERBARU (TABLE WIDGET)
 * ============================================================================
 * Menampilkan 5 transaksi penerimaan servis terbaru secara realtime bagi Owner:
 * - Nomor servis (kode unik)
 * - Identitas pelanggan dan nomor telepon
 * - Nama perangkat & model
 * - Status pengerjaan (badge warna otomatis)
 * - Total tagihan & status pembayaran invoice (Lunas / Sebagian / Belum Bayar)
 * - Tanggal masuk relatif (time-ago)
 * - Tautan langsung menuju halaman kelola / detail servis
 */
class OwnerRecentRepairsWidget extends BaseWidget
{
    // Urutan posisi widget (bagian bawah dashboard)
    protected static ?int $sort = 5;

    // Lebar kolom penuh layar
    protected int | string | array $columnSpan = 'full';

    // Judul kartu tabel
    protected static ?string $heading = 'Servis Masuk Terbaru';

    /**
     * Hak Akses:
     * Hanya role 'owner' atau bypass role yang diizinkan melihat tabel ini.
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
     * Konfigurasi tabel Filament: Query, Kolom, Aksi Header, dan Link Baris.
     */
    public function table(Table $table): Table
    {
        // ---------------------------------------------------------------------
        // 1. Query Data Servis dengan Eager Loading Relasi Terkait
        // ---------------------------------------------------------------------
        $branchId = session('current_branch_id');

        $query = Repair::query()
            ->with([
                'client',               // Data pelanggan & kontak
                'repairDevices.brand',  // Data merek dan model perangkat
                'repairInvoice',        // Tagihan dan status pembayaran
                'branch',               // Informasi cabang
            ])
            ->latest('id');

        // Filter berdasarkan cabang aktif bila ada
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $table
            ->query($query->limit(5))
            // -----------------------------------------------------------------
            // 2. Definisi Kolom-kolom Tabel
            // -----------------------------------------------------------------
            ->columns([
                // Kolom 1: Nomor Servis (Teks tebal)
                TextColumn::make('code')
                    ->label('No. Servis')
                    ->weight('bold')
                    ->searchable(),

                // Kolom 2: Nama Pelanggan & Nomor Telepon
                TextColumn::make('client.name')
                    ->label('Pelanggan')
                    ->description(fn (Repair $record): ?string => $record->client?->phone ?? '-'),

                // Kolom 3: Ringkasan Perangkat / Kerusakan
                TextColumn::make('devices_summary')
                    ->label('Perangkat / Kerusakan')
                    ->state(function (Repair $record): string {
                        $devices = $record->repairDevices;
                        if ($devices->isEmpty()) {
                            return '-';
                        }
                        return $devices->map(function ($dev) {
                            $brandName = $dev->brand?->name ?? '';
                            $model = $dev->model ?? '';
                            return trim("{$brandName} {$model}");
                        })->filter()->implode(', ');
                    }),

                // Kolom 4: Badge Status Servis (Otomatis mengambil warna & label dari enum RepairStatus)
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                // Kolom 5: Total Tagihan Biaya Servis
                TextColumn::make('repairInvoice.grand_total')
                    ->label('Total Tagihan')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'Rp ' . number_format($state, 0, ',', '.') : '-')
                    ->alignEnd(),

                // Kolom 6: Status Pembayaran Invoice (Lunas / Sebagian / Belum Bayar)
                TextColumn::make('repairInvoice.status')
                    ->label('Status Bayar')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'paid' => 'Lunas',
                        'partially_paid' => 'Sebagian',
                        'unpaid' => 'Belum Bayar',
                        default => 'Belum Ada Invoice',
                    })
                    ->color(fn ($state) => match ($state) {
                        'paid' => 'success',
                        'partially_paid' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    }),

                // Kolom 7: Waktu Masuk (Format relatif, misal: '1 day ago')
                TextColumn::make('created_at')
                    ->label('Waktu Masuk')
                    ->since()
                    ->sortable(),
            ])
            // -----------------------------------------------------------------
            // 3. Tombol Aksi di Header Tabel (Shortcut ke Daftar Lengkap)
            // -----------------------------------------------------------------
            ->headerActions([
                Action::make('view_all')
                    ->label('Lihat Semua Servis')
                    ->url(fn (): string => RepairResource::getUrl('index'))
                    ->icon('heroicon-m-arrow-right'),
            ])
            // Klik pada baris tabel mengarahkan ke halaman servis dengan filter kode terkait
            ->recordUrl(fn (Repair $record): string => RepairResource::getUrl('index', ['tableSearch' => $record->code]))
            ->paginated(false);
    }
}
