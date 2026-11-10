<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Forms\Components\GeofenceMap;
use Filament\Pages\Page;

// use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use App\Models\Tenant\Setting;
use App\Models\Tenant\FeatureLimit;
use App\Models\Tenant\FeatureToggle;
use Spatie\Permission\Models\Role;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth; // 'heroicon-o-cog-6-tooth';
    protected static string|null $navigationLabel = 'Pengaturan Umum';

    protected string $view = 'filament.tenant.pages.settings-page';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = [
            'tenant_bypass_roles' => settingTenant('tenant_bypass_roles', ['owner']),
            'tenant_cs_roles' => settingTenant('tenant_cs_roles', ['customer_service']),
            'tenant_technical_roles' => settingTenant('tenant_technical_roles', ['technician']),
            'invoice_prefix' => settingTenant('invoice_prefix', 'INV'),
            'repair_prefix' => settingTenant('repair_prefix', 'REP'),
            'po_prefix' => settingTenant('po_prefix', 'PO'),
            'po_dp_prefix' => settingTenant('po_dp_prefix', 'PO-DP'),
            'pay_debt_prefix' => settingTenant('pay_debt_prefix', 'PAY'),
            'goods_receipt_prefix' => settingTenant('goods_receipt_prefix', 'GR'),
            'stock_transfer_prefix' => settingTenant('stock_transfer_prefix', 'TRF'),
            'journal_prefix' => settingTenant('journal_prefix', 'JRN'),
            'pos_prefix' => settingTenant('pos_prefix', 'POS'),
            'pay_repair_prefix' => settingTenant('pay_repair_prefix', 'PAY'),
            'color_primary' => settingTenant('color_primary', '#007bff'),
            'default_latitude' => settingTenant('default_latitude', '-7.258137'),
            'default_longitude' => settingTenant('default_longitude', '112.749306'),
            'default_attendance_radius' => (int) settingTenant('default_attendance_radius', 10),
            'brand_name' => settingTenant('brand_name', 'Schienetren'),
            'default_client_name' => settingTenant('default_client_name', 'Pelanggan Umum'),

            // Struk Kasir & Nota Pengambilan & WhatsApp Template
            'pos_receipt_footer_title' => settingTenant('pos_receipt_footer_title', 'Terima Kasih Atas Kunjungan Anda!'),
            'pos_receipt_footer_notes' => settingTenant('pos_receipt_footer_notes', 'Barang yang sudah dibeli dapat ditukar jika bergaransi resmi sesuai ketentuan toko.'),
            'repair_uncollected_days_limit' => (int) settingTenant('repair_uncollected_days_limit', 30),
            'repair_terms_and_conditions' => settingTenant('repair_terms_and_conditions', "1. Nota ini adalah bukti sah pengambilan perangkat. Harap dibawa saat mengambil perangkat.\n2. Garansi berlaku sesuai dengan kesepakatan jenis perbaikan dan tidak berlaku untuk kerusakan akibat kelalaian pengguna (jatuh, kena air, dll).\n3. Perangkat yang tidak diambil lebih dari 30 hari sejak pemberitahuan selesai, di luar tanggung jawab kami.\n4. Segel garansi yang rusak atau robek akan membatalkan garansi."),
            'repair_whatsapp_reminder_template' => settingTenant('repair_whatsapp_reminder_template', "Halo Kak {customer_name},\n\nKami menginformasikan bahwa tagihan servis Anda di {brand_name} (Tiket: {ticket_code}) memiliki sisa piutang sebesar *Rp {amount}* dengan tanggal jatuh tempo *{due_date}*.\n\nMohon konfirmasi atau pelunasan saat pengambilan unit. Terima kasih!"),

            // Pemetaan Akun Keuangan & Akuntansi (COA)
            'finance_account_cash' => $this->getValidAccountId('finance_account_cash', 1),
            'finance_account_inventory' => $this->getValidAccountId('finance_account_inventory', 2),
            'finance_account_ap' => $this->getValidAccountId('finance_account_ap', 5),
            'finance_account_revenue_repair' => $this->getValidAccountId('finance_account_revenue_repair', 4),
            'finance_account_salary_expense' => $this->getValidAccountId('finance_account_salary_expense', 6),
            'finance_account_general_expense' => $this->getValidAccountId('finance_account_general_expense', 7),
        ];

        // Ambil semua lisensi fitur (limit & boolean) yang di-push dari pusat
        try {
            $featureLimits = FeatureLimit::all();
            $featureToggles = FeatureToggle::pluck('is_enabled', 'feature_code')->toArray();
        } catch (\Throwable $e) {
            $featureLimits = collect();
            $featureToggles = [];
        }

        foreach ($featureLimits as $limit) {
            // Jika ada di toggles, pakai nilai tersebut. Jika belum ada, default true.
            $settings['feature_toggle_' . $limit->feature_code] = $featureToggles[$limit->feature_code] ?? true;
        }

        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        try {
            $featureLimits = FeatureLimit::all();
        } catch (\Throwable $e) {
            $featureLimits = collect();
        }
        $featureToggleComponents = [];

        foreach ($featureLimits as $limit) {
            $label = Str::title(str_replace('_', ' ', $limit->feature_code));
            // Tambahkan keterangan limit di label jika dia tipe limit
            if ($limit->feature_type === 'limit' && $limit->max_value) {
                $label .= " (Max: {$limit->max_value})";
            }

            $featureToggleComponents[] = Toggle::make('feature_toggle_' . $limit->feature_code)
                ->label($label);
        }

        $tabs = [
            Tab::make('Umum')
                ->icon(Heroicon::OutlinedBuildingStorefront)
                ->schema([
                    Section::make('Identitas Brand & Tampilan')
                        ->description('Konfigurasi nama toko dan warna tampilan website.')
                        ->columns(3)
                        ->schema([
                            TextInput::make('brand_name')
                                ->label('Brand Name')
                                ->placeholder('Schienetren'),


                            ColorPicker::make('color_primary')
                                ->label('Warna Utama Website')
                                ->nullable(),
                        ]),
                ]),

            Tab::make('Prefix Dokumen')
                ->icon(Heroicon::OutlinedDocumentText)
                ->schema([
                    Section::make('Prefix Penomoran Dokumen')
                        ->description('Format awalan kode otomatis untuk penomoran dokumen dan transaksi.')
                        ->columns(3)
                        ->schema([
                            TextInput::make('invoice_prefix')
                                ->label('Prefix Invoice')
                                ->placeholder('INV')
                                ->helperText('Contoh: INV -> INV-CABANG-000001'),
                            TextInput::make('repair_prefix')
                                ->label('Prefix Servis / Repair')
                                ->placeholder('REP')
                                ->helperText('Contoh: REP -> REP-CABANG-000001'),
                            TextInput::make('po_prefix')
                                ->label('Prefix Purchase Order (PO)')
                                ->placeholder('PO')
                                ->helperText('Contoh: PO -> PO-20261013-XXXX'),
                            TextInput::make('po_dp_prefix')
                                ->label('Prefix Uang Muka PO (DP)')
                                ->placeholder('PO-DP')
                                ->helperText('Contoh: PO-DP -> PO-DP-PO-20261018-XXXX'),
                            TextInput::make('pay_debt_prefix')
                                ->label('Prefix Bayar Hutang Supplier')
                                ->placeholder('PAY')
                                ->helperText('Contoh: PAY -> PAY-PO-20261018-XXXX-...'),
                            TextInput::make('goods_receipt_prefix')
                                ->label('Prefix Penerimaan Barang (GR)')
                                ->placeholder('GR')
                                ->helperText('Contoh: GR -> GR-20261013-XXXX'),
                            TextInput::make('stock_transfer_prefix')
                                ->label('Prefix Mutasi Stok (TRF)')
                                ->placeholder('TRF')
                                ->helperText('Contoh: TRF -> TRF-202610-XXXX'),
                            TextInput::make('journal_prefix')
                                ->label('Prefix Jurnal Akuntansi (JRN)')
                                ->placeholder('JRN')
                                ->helperText('Contoh: JRN -> JRN-202610-0001'),
                            TextInput::make('pos_prefix')
                                ->label('Prefix Point of Sale (POS)')
                                ->placeholder('POS')
                                ->helperText('Contoh: POS -> POS-CABANG-20261017-0001'),
                            TextInput::make('pay_repair_prefix')
                                ->label('Prefix Pelunasan Tagihan Servis')
                                ->placeholder('PAY')
                                ->helperText('Contoh: PAY -> PAY-20261018-XXXX'),
                        ]),
                ]),

            Tab::make('Keuangan & COA')
                ->icon(Heroicon::OutlinedBanknotes)
                ->visible(fn () => tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE))
                ->schema([
                    Section::make('Pemetaan Akun Keuangan & Akuntansi (Chart of Accounts)')
                        ->description('Pemetaan akun default untuk integrasi pembukuan otomatis (Jurnal Debit/Kredit dan Beban Operasional).')
                        ->columns(3)
                        ->schema([
                            Select::make('finance_account_cash')
                                ->label('Akun Kas / Bank Utama')
                                ->options(fn () => \App\Models\Tenant\FinanceAccount::all()
                                    ->mapWithKeys(fn ($acc) => [$acc->id => "{$acc->code} - {$acc->name}"])
                                    ->toArray()
                                )
                                ->searchable()
                                ->nullable()
                                ->placeholder('Pilih Akun Kas...')
                                ->helperText('Akun kas default untuk pembayaran DP, cicilan hutang, dan operasional.'),

                            Select::make('finance_account_inventory')
                                ->label('Akun Persediaan Barang')
                                ->options(fn () => \App\Models\Tenant\FinanceAccount::all()
                                    ->mapWithKeys(fn ($acc) => [$acc->id => "{$acc->code} - {$acc->name}"])
                                    ->toArray()
                                )
                                ->searchable()
                                ->nullable()
                                ->placeholder('Pilih Akun Persediaan...')
                                ->helperText('Akun aset persediaan saat barang diterima via Goods Receipt.'),

                            Select::make('finance_account_ap')
                                ->label('Akun Hutang Usaha (AP)')
                                ->options(fn () => \App\Models\Tenant\FinanceAccount::all()
                                    ->mapWithKeys(fn ($acc) => [$acc->id => "{$acc->code} - {$acc->name}"])
                                    ->toArray()
                                )
                                ->searchable()
                                ->nullable()
                                ->placeholder('Pilih Akun Hutang...')
                                ->helperText('Akun liabilitas hutang supplier saat pembelian tempo / PO.'),

                            Select::make('finance_account_revenue_repair')
                                ->label('Akun Pendapatan Servis')
                                ->options(fn () => \App\Models\Tenant\FinanceAccount::all()
                                    ->mapWithKeys(fn ($acc) => [$acc->id => "{$acc->code} - {$acc->name}"])
                                    ->toArray()
                                )
                                ->searchable()
                                ->nullable()
                                ->placeholder('Pilih Akun Pendapatan...')
                                ->helperText('Akun kredit penerimaan pendapatan dari jasa servis & repair.'),

                            Select::make('finance_account_salary_expense')
                                ->label('Akun Beban Gaji Karyawan')
                                ->options(fn () => \App\Models\Tenant\FinanceAccount::all()
                                    ->mapWithKeys(fn ($acc) => [$acc->id => "{$acc->code} - {$acc->name}"])
                                    ->toArray()
                                )
                                ->searchable()
                                ->nullable()
                                ->placeholder('Pilih Akun Beban Gaji...')
                                ->helperText('Akun debit beban penggajian karyawan saat slip gaji dibayar.'),

                            Select::make('finance_account_general_expense')
                                ->label('Akun Beban Operasional Umum')
                                ->options(fn () => \App\Models\Tenant\FinanceAccount::all()
                                    ->mapWithKeys(fn ($acc) => [$acc->id => "{$acc->code} - {$acc->name}"])
                                    ->toArray()
                                )
                                ->searchable()
                                ->nullable()
                                ->placeholder('Pilih Akun Beban Operasional...')
                                ->helperText('Akun debit default untuk pengeluaran kas operasional toko lainnya.'),
                        ]),
                ]),

            Tab::make('Hak Akses & Lokasi')
                ->icon(Heroicon::OutlinedUserGroup)
                ->schema([
                    Section::make('Role Operasional')
                        ->description('Pengaturan hak akses role untuk pengecualian dan operasional.')
                        ->columns(3)
                        ->schema([
                            Select::make('tenant_bypass_roles')
                                ->label('Role Pengecualian (Unlimited Access)')
                                ->multiple()
                                ->options(fn () => Role::pluck('name', 'name'))
                                ->helperText('Role yang memiliki akses penuh untuk admin.'),

                            Select::make('tenant_cs_roles')
                                ->label('Role Customer Service')
                                ->multiple()
                                ->options(fn () => Role::pluck('name', 'name'))
                                ->helperText('Role untuk customer service.'),

                            Select::make('tenant_technical_roles')
                                ->label('Role Technical Support')
                                ->multiple()
                                ->options(fn () => Role::pluck('name', 'name'))
                                ->helperText('Role untuk teknisi.'),
                        ]),

                    Section::make('Default Location')
                        ->description('Set koordinat default untuk sistem absensi dan lokasi pusat.')
                        ->columns(3)
                        ->schema([
                            TextInput::make('default_latitude')
                                ->label('Default Latitude')
                                ->numeric()
                                ->required(),
                            TextInput::make('default_longitude')
                                ->label('Default Longitude')
                                ->numeric()
                                ->required(),
                            TextInput::make('default_attendance_radius')
                                ->label('Radius Absensi (Meter)')
                                ->numeric()
                                ->default(10)
                                ->helperText('Radius toleransi default saat membuat cabang baru.')
                                ->required(),
                            GeofenceMap::make('default_location_map')
                                ->label('Pilih Lokasi di Peta')
                                ->columnSpanFull()
                                ->live()
                                ->latitudeField('default_latitude')
                                ->longitudeField('default_longitude')
                                ->config(fn() => [
                                    'mode' => 'admin',
                                    'zoom' => 15,
                                    'height' => 200,
                                ])
                                ->dehydrated(false),
                        ]),
                ]),

            Tab::make('Nota & Notifikasi')
                ->icon(Heroicon::OutlinedPrinter)
                ->schema([
                    Section::make('Struk Belanja Kasir (POS)')
                        ->description('Teks informasi dan catatan kaki pada cetakan struk kasir thermal.')
                        ->columns(1)
                        ->schema([
                            TextInput::make('pos_receipt_footer_title')
                                ->label('Judul Penutup Struk')
                                ->placeholder('Terima Kasih Atas Kunjungan Anda!'),
                            Textarea::make('pos_receipt_footer_notes')
                                ->label('Catatan Kaki Struk / Syarat Retur')
                                ->rows(2)
                                ->placeholder('Barang yang sudah dibeli dapat ditukar jika bergaransi resmi sesuai ketentuan toko.'),
                        ]),

                    Section::make('Nota Pengambilan & Garansi Servis')
                        ->description('Format syarat & ketentuan klaim garansi pada cetak nota pengambilan unit servis.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('repair_uncollected_days_limit')
                                ->label('Batas Maksimal Pengambilan Unit (Hari)')
                                ->numeric()
                                ->default(30)
                                ->helperText('Batas waktu hari setelah unit selesai diperbaiki sebelum di luar tanggung jawab toko.'),
                            Textarea::make('repair_terms_and_conditions')
                                ->label('Klausul Syarat & Ketentuan Servis')
                                ->columnSpanFull()
                                ->rows(4)
                                ->helperText('Poin-poin aturan garansi dan pengambilan yang tercetak di bagian bawah nota.'),
                        ]),

                    Section::make('Template WhatsApp Pengingat Tagihan')
                        ->description('Format template pesan otomatis saat staf mengirim tagihan piutang via WA.')
                        ->schema([
                            Textarea::make('repair_whatsapp_reminder_template')
                                ->label('Pesan WhatsApp')
                                ->rows(4)
                                ->helperText('Variabel yang didukung: {customer_name}, {brand_name}, {ticket_code}, {amount}, {due_date}'),
                        ]),
                ]),
        ];

        if (count($featureToggleComponents) > 0) {
            $tabs[] = Tab::make('Fitur & Modul')
                ->icon(Heroicon::OutlinedPuzzlePiece)
                ->schema([
                    Section::make('Feature Configuration')
                        ->description('Aktifkan atau nonaktifkan modul/fitur yang ingin digunakan oleh cabang ini.')
                        ->headerActions([
                            \Filament\Actions\Action::make('selectAllFeatures')
                                ->label('Select All')
                                ->icon('heroicon-o-check-circle')
                                ->action(function ($set) {
                                    foreach (FeatureLimit::pluck('feature_code') as $code) {
                                        $set('feature_toggle_' . $code, true);
                                    }
                                }),
                            \Filament\Actions\Action::make('deselectAllFeatures')
                                ->label('Deselect All')
                                ->icon('heroicon-o-x-circle')
                                ->color('danger')
                                ->action(function ($set) {
                                    foreach (FeatureLimit::pluck('feature_code') as $code) {
                                        $set('feature_toggle_' . $code, false);
                                    }
                                }),
                        ])
                        ->columns(3)
                        ->schema($featureToggleComponents),
                ]);
        }

        return $schema
            ->components([
                Tabs::make('Pengaturan Tenant')
                    ->tabs($tabs)
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'feature_toggle_')) {
                $featureCode = str_replace('feature_toggle_', '', $key);
                
                // Simpan state on/off tenant secara mandiri
                FeatureToggle::updateOrCreate(
                    ['feature_code' => $featureCode],
                    ['is_enabled' => $value]
                );
            } else {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();

        // Clear the cache
        app(\Illuminate\Contracts\Cache\Repository::class)->forget('settings_tenant_' . tenancy()->tenant->id);

        redirect(request()->header('Referer') ?: static::getUrl());
    }

    protected function getValidAccountId(string $key, ?int $fallbackId = null): ?int
    {
        $val = settingTenant($key);
        $id = $val !== null ? (int) $val : $fallbackId;

        if ($id !== null) {
            try {
                if (\App\Models\Tenant\FinanceAccount::where('id', $id)->exists()) {
                    return $id;
                }
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])) ?? false;
    }
}
