# Schienetren — Vertical SaaS ERP Service & Retail Multi-Branch

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="220" alt="Laravel Logo">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/Filament-v5-FDAE4B?style=for-the-badge&logo=filament&logoColor=black" alt="Filament PHP v5">
  <img src="https://img.shields.io/badge/PHP-^8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/Multi--Tenancy-Database--per--Tenant-007ACC?style=for-the-badge" alt="Database per Tenant">
  <img src="https://img.shields.io/badge/Database-MySQL%208-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License MIT">
</p>

---

## ?? 1. Tentang Aplikasi (Overview)

**Schienetren** adalah platform **Vertical SaaS ERP** yang dikembangkan khusus untuk **Jasa Reparasi Elektronik** (Laptop, Komputer, Smartphone, Konsol/Gadget) dan **Toko Retail Komputer Multi-Cabang**.

Berbeda dari ERP horizontal generik yang memerlukan penyesuaian rumit, Schienetren dirancang *out-of-the-box* untuk alur kerja nyata bisnis servis teknis:
- Penerimaan unit fisik (*intake*) dengan checklist kondisi dan dokumentasi foto.
- Pembagian kerja teknisi berbasis **Kanban Board** drag-and-drop.
- **Gerbang Quality Control (QC Gate)** wajib sebelum unit dapat diserahkan.
- **Berita acara serah terima dengan Tanda Tangan Digital** langsung dari layar sentuh/mouse.
- Otomatisasi **komisi teknisi** terhubung langsung ke modul penggajian.
- Penanganan **Garansi, Retur Suku Cadang, dan Klaim RMA**.
- Pelacakan status pengerjaan transparan tanpa login oleh pelanggan.

### ??? Arsitektur Multi-Tenancy (Database-per-Tenant)

Menggunakan paket `stancl/tenancy` dengan pendekatan **Database-per-Tenant**:

1. **Central Database (`schienetren`):** Menyimpan akun super admin, paket langganan, fitur & limit, wallet, invoice, promo, dan daftar tenant/domain.
2. **Tenant Databases (`tenant__{id}`):** Setiap mitra bisnis memiliki database MySQL fisik terpisah secara otomatis, menjamin:
   - **Isolasi Keamanan 100%:** Data antar-toko tidak tercampur di tingkat basis data.
   - **Performa Tinggi:** Ukuran tabel tidak membengkak karena dibagi per tenant.
   - **Kemudahan Backup & Restore:** Per entitas bisnis, tanpa mengganggu tenant lain.

### ??? Struktur 3 Panel Filament + 1 Portal Publik

1. **Central Admin Panel** (`schienetren.test/admin`): Pengelolaan paket, fitur, addon, promo/kupon, verifikasi pembayaran, dan aktivasi tenant.
2. **Customer Portal Panel** (`schienetren.test/portal`): Self-service pemilik bisnis untuk registrasi, pilih paket, checkout dengan kode promo, dan upload bukti transfer.
3. **Tenant Operations Panel** (`lalacom.schienetren.test/admin`): Ruang kerja harian tim operasional toko dengan **Branch Switcher** dan **Dashboard per Peran** (Owner, CS/Kasir, Teknisi).
4. **Public Repair Tracking** (`lalacom.schienetren.test/cek-status`): Halaman publik dilindungi *rate limiter* untuk pelanggan melacak progres servis.

---

## ? 2. Fitur-Fitur Sistem

### ??? A. Manajemen Servis & Reparasi
- **Intake & Diagnosa:** Pencatatan keluhan, estimasi biaya, checklist kondisi fisik, dan dokumentasi foto sebelum unit dikerjakan.
- **Papan Kanban Teknisi:** Visualisasi alur reparasi drag-and-drop (Antrian ? Diagnosa ? Menunggu Konfirmasi/Sparepart ? Pengerjaan ? QC ? Selesai).
- **QC Gate:** Checklist pengujian wajib lolos sebelum status bisa diubah ke "siap diambil".
- **Serah Terima & Digital Signature:** Tanda tangan digital pelanggan via `filament-autograph`.
- **Cetak Dokumen:** Nota Tanda Terima Masuk dan Nota Pengambilan/Faktur Servis.
- **Garansi & Klaim RMA:** Pelacakan masa garansi jasa & suku cadang, klaim ulang, retur sparepart cacat, hingga refund.
- **Komisi Teknisi:** Kalkulasi bagi hasil otomatis saat servis selesai/lunas, terhubung ke payroll.

### ?? B. Manajemen Inventaris & Rantai Pasok
- **Master Data:** Kategori, Brand, Unit satuan, Master Produk, dan Varian.
- **Pelacakan Serial Number (SN):** Manajemen nomor serial unik untuk pelacakan garansi dan riwayat perpindahan.
- **Purchase Order (PO) & Goods Receipt:** Siklus pengadaan ke supplier dengan auto-input SN ke stok cabang.
- **Transfer Stok Antar-Cabang:** Distribusi barang dengan validasi SN keluar dan verifikasi terima di cabang tujuan.
- **Stock Opname & Audit Fisik:** Audit berkala (Draft ? In-Progress ? Verified) dengan rekonsiliasi otomatis ke riwayat stok.
- **Kasir POS Ritel:** Penjualan sparepart/aksesori, cetak struk, pelacakan stok real-time.

### ?? C. SDM & Presensi
- **Penugasan Multi-Cabang:** Satu karyawan dapat ditugaskan ke beberapa cabang dengan peran dan shift berbeda.
- **Presensi Geofencing GPS & Selfie:** Absensi berbasis radius koordinat peta Leaflet dan verifikasi swafoto kamera live.
- **Manajemen Jadwal & Shift:** Shift fleksibel, jadwal libur nasional, lembur, dan override shift.
- **Cuti & Izin:** Pengajuan cuti dengan kuota saldo tahunan dan upload bukti lampiran.
- **Payroll Otomatis:** Slip gaji bulanan: `Gaji Pokok + Tunjangan/Kompensasi + Komisi Riil Servis - Potongan Keterlambatan/Absen`.

### ?? D. Keuangan & Akuntansi (Dual-Mode)
- **Finance Simple:**
  - Kas kecil & pengeluaran operasional harian.
  - Pembayaran hutang invoice pembelian ke supplier.
  - Monitoring piutang servis pelanggan yang belum lunas.
  - Ringkasan pemasukan, pengeluaran, dan estimasi laba kotor harian.
- **Finance Advance (Double-Entry):**
  - Chart of Accounts (CoA) standar akuntansi.
  - Jurnal otomatis dari setiap transaksi (kasir, servis, PO) + entri manual Debit/Kredit.
  - Shifting kasir (buka & tutup laci) dengan pencatatan selisih kas fisik.
  - Laporan: Buku Besar, Neraca Saldo, Laba/Rugi, dan Neraca Keuangan.

### ?? E. Tata Kelola Multi-Cabang & RBAC
- **Branch Switcher:** Ganti cabang aktif via header tanpa relogin.
- **Dashboard per Peran:** Dasbor berbeda untuk Owner (revenue chart, financial overview), CS/Kasir (daftar servis aktif), dan Teknisi (antrian pekerjaan).
- **RBAC Granular:** Berbasis `spatie/laravel-permission` (Owner, Branch Manager, Kasir/CS, Teknisi Senior, Teknisi Junior, Finance, Gudang).

### ?? F. Platform SaaS (Central & Portal)
- **Paket Langganan:** Konfigurasi batas pengguna, cabang, produk, dan fitur aktif per paket.
- **Addon & Topup:** Penambahan kuota cabang atau fitur secara fleksibel di luar paket.
- **Promo & Kode Kupon:** Kode promo (fixed/persentase, target servis/ritel, batas penggunaan) yang dapat dipakai tenant saat checkout.
- **Wallet Tenant:** Saldo internal tenant; topup via transfer manual yang diverifikasi admin.
- **Checkout & Konfirmasi Pembayaran:** Registrasi bisnis, pilih paket, upload bukti transfer, verifikasi admin.

### ?? G. Laporan Lanjutan (Advanced Reports)
Empat modul laporan dengan filter tanggal, cabang, dan ekspor data:
- **Laporan Servis:** Performa teknisi, tren pendapatan servis, distribusi status reparasi.
- **Laporan Keuangan:** Arus kas, jurnal, saldo akun, laba/rugi per periode.
- **Laporan SDM:** Rekap absensi, kinerja cuti, distribusi shift karyawan.
- **Laporan Inventaris:** Pergerakan stok, produk terlaris, nilai inventaris, audit opname.

### ??? H. Feature Gating & Graceful Degradation

Schienetren mengadopsi **Feature Gating** berbasis paket langganan dan sakelar mandiri tenant (`tenant()->hasFeatureCode(...)`). Ketika fitur dinonaktifkan, sistem menerapkan **Graceful Degradation** agar operasional dasar tetap berjalan tanpa *crash*.

| No | Kode Fitur | Tipe | Nama Fitur | Perilaku Saat Nonaktif (Fallback) |
|:--:| :--- | :---: | :--- | :--- |
| 1 | `TENANT` | limit | Maksimal Bisnis Toko | Pembuatan toko baru diblokir jika kuota habis. |
| 2 | `MAX_USER` | limit | Kuota Staf / Pengguna | Tombol tambah user dinonaktifkan jika mencapai kuota. |
| 3 | `MAX_BRANCH` | limit | Kuota Cabang Toko | Pembuatan cabang baru diblokir saat batas tercapai. |
| 4 | `MAX_PRODUCT` | limit | Kuota Katalog Produk | Tambah produk/sparepart baru diblokir saat batas tercapai. |
| 5 | `MAX_MONTHLY_REPAIR` | limit | Kuota Servis / Bulan | Form penerimaan unit baru terkunci hingga pergantian bulan. |
| 6 | `KANBAN_TECHNICIAN` | boolean | Papan Kanban Teknisi | Menu Kanban disembunyikan; progres dikelola lewat tabel antrian. |
| 7 | `POS` | boolean | Kasir Penjualan Ritel | Menu POS & Penjualan Ritel disembunyikan. |
| 8 | `PURCHASE_ORDER` | boolean | Pengadaan & PO | Menu Purchase Order & Goods Receipt disembunyikan. |
| 9 | `STOCK_TRANSFER` | boolean | Mutasi Stok Antar Cabang | Menu transfer stok disembunyikan. |
| 10 | `STOCK_OPNAME` | boolean | Audit & Penyesuaian Stok | Menu Stock Opname disembunyikan. |
| 11 | `FINANCE_ADVANCE` | boolean | Keuangan Double-Entry | Menu Jurnal, CoA, & Laporan disembunyikan; fallback ke Finance Simple. |
| 12 | `LEAVE` | boolean | Pengajuan Cuti | Menu Cuti disembunyikan; absensi murni evaluasi jam hadir/alpa. |
| 13 | `HOLIDAY` | boolean | Kalender Libur Nasional | Menu Hari Libur disembunyikan; libur mengikuti shift reguler. |
| 14 | `SHIFT_MANAGEMENT` | boolean | Manajemen Shift Kerja | Menu Shift disembunyikan; absensi fallback ke jadwal standar Senin–Sabtu 08:00–17:00. |
| 15 | `PAYROLL` | boolean | Penggajian Otomatis | Menu Payroll disembunyikan; kalkulasi bulanan dinonaktifkan. |
| 16 | `ATTENDANCE_GEOFENCE` | boolean | Presensi Geofencing GPS | Presensi berjalan tanpa pengecekan radius koordinat. |
| 17 | `SKILL_MANAGEMENT` | boolean | Matriks Skill Teknisi | Menu Skill & Spesialisasi disembunyikan. |
| 18 | `ADVANCED_REPORTS` | boolean | Laporan Lanjutan | Menu laporan lanjutan disembunyikan; tenant hanya dapat ringkasan dasbor bawaan. |

> [!NOTE]
> Untuk dokumentasi checklist teknis implementasi (Policy, Model Hook, Observers, Form Schema), lihat [tenant_features_master_plan.md](docs/tenant_features_master_plan.md).

---

## ?? 3. Kebutuhan Sistem

| Kebutuhan | Versi Minimal | Keterangan |
| :--- | :--- | :--- |
| **PHP** | `>= 8.3` | Direkomendasikan PHP 8.3 atau 8.4 |
| **Ekstensi PHP** | - | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`, `fileinfo`, `gd` / `imagick` |
| **Basis Data** | MySQL `>= 8.0` / MariaDB `>= 10.4` | User DB butuh hak `CREATE DATABASE` untuk multi-tenant otomatis |
| **Composer** | `>= 2.x` | Pengelolaan pustaka PHP |
| **Node.js & NPM** | Node `>= 18.x`, NPM `>= 9.x` | Kompilasi aset Vite & Tailwind CSS |
| **Web Server** | Laragon / Nginx / Apache | **Laragon** direkomendasikan di Windows untuk auto-resolving `*.test` |

---

## ?? 4. Instalasi

### Langkah 1: Clone Repository
```bash
cd C:\laragon\www
git clone https://github.com/18050623023/schienetren-public.git
cd schienetren
```

### Langkah 2: Salin & Edit `.env`
```bash
cp .env.example .env
```
Sesuaikan variabel berikut:
```env
APP_URL=http://schienetren.test

TENANCY_CENTRAL_DOMAIN=schienetren.test
TENANCY_DOMAIN={tenant}.schienetren.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=schienetren
DB_USERNAME=root
DB_PASSWORD=
```

> [!IMPORTANT]
> `DB_USERNAME` harus memiliki hak `CREATE DATABASE` agar database tenant terisolasi dapat dibuat otomatis.

### Langkah 3: Konfigurasi Virtual Host & Local Domain
Jika menggunakan **Laragon**: restart agar `schienetren.test` otomatis terdeteksi.

Tambahkan ke `C:\Windows\System32\drivers\etc\hosts` (Run as Administrator):
```text
127.0.0.1 schienetren.test
127.0.0.1 lalacom.schienetren.test
```

### Langkah 4–10: Instalasi Lengkap
```bash
# 4. Install dependensi PHP
composer install

# 5. Generate app key
php artisan key:generate

# 6. Buat database central di MySQL client:
# CREATE DATABASE IF NOT EXISTS schienetren CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 7. Migrasi & seeder central (buat akun Super Admin, paket, hak akses)
php artisan migrate --seed

# 8a. [Via Portal] Registrasi tenant di http://schienetren.test/portal/register
# 8b. [Via Artisan] Jika record tenant sudah ada di database central:
php artisan tenants:migrate
php artisan tenants:seed --class=TenantDatabaseSeeder

# 9. Install frontend & build aset
npm install && npm run build
# atau mode dev:
npm run dev

# 10. Buat storage symlink
php artisan storage:link
```

---

## ?? 5. Akun Default

| Panel | URL | Email | Password | Role |
| :--- | :--- | :--- | :--- | :--- |
| **Central Admin** | `http://schienetren.test/admin` | `admin@admin.com` | `password` | Super Administrator |
| **Customer Portal** | `http://schienetren.test/portal` | *(registrasi mandiri)* | — | Pemilik Bisnis Mitra |
| **Tenant Operations** | `http://lalacom.schienetren.test/admin` | `admin@tenant.com` | `password` | Owner Tenant |
| **Tracking Publik** | `http://lalacom.schienetren.test/cek-status` | *(tanpa login)* | — | Pelanggan |

---

## ?? 6. Struktur Direktori Utama

```text
schienetren/
+-- app/
¦   +-- Console/Commands/    # Artisan commands: payroll, auto-renew, storage cleaner, dll.
¦   +-- Enums/               # TenantFeature, RepairStatus, RepairDeviceStatus, dll.
¦   +-- Filament/
¦   ¦   +-- Admin/           # Resource & Widget Central Admin Panel
¦   ¦   +-- Portal/          # Halaman Checkout, Billing Portal Mitra
¦   ¦   +-- Tenant/
¦   ¦       +-- Pages/       # Dashboard (per-role), POS, Kanban, Absensi, Laporan, Settings
¦   ¦       +-- Resources/   # 44 resource: Repair, Inventory, HR, Finance, dll.
¦   ¦       +-- Widgets/     # Owner charts, Daily Operations, Leave Balance, dll.
¦   +-- Jobs/                # SetupTenant (async provisioning database tenant baru)
¦   +-- Livewire/            # BranchSwitcher, ClientRepairStatus, InventoryStockPicker, dll.
¦   +-- Models/
¦   ¦   +-- *.php            # Central Models (Tenant, User, Plan, Subscription, Wallet, Promo, dll.)
¦   ¦   +-- Tenant/          # 78 Tenant Models (Repair, Product, Stock, Employee, Finance, dll.)
¦   +-- Observers/Tenant/    # RepairPaymentObserver, PurchaseOrderObserver, dll.
¦   +-- Policies/            # Policy RBAC per resource
¦   +-- Providers/
¦   ¦   +-- Filament/        # AdminPanelProvider, PortalPanelProvider, TenantPanelProvider
¦   ¦   +-- TenancyServiceProvider.php
¦   +-- Services/
¦       +-- AttendanceService.php
¦       +-- BillingService.php
¦       +-- FinanceAccountingService.php
¦       +-- PayrollCalculationService.php
¦       +-- Stock/           # StockAdjustmentService, StockMovementService
¦       +-- Pos/             # PosCheckoutService
¦       +-- Reports/         # FinanceReport, HrReport, InventoryReport, ServiceReport
¦       +-- Payment/         # ManualTransferService (aktif), MidtransService (planned)
+-- config/
¦   +-- tenancy.php          # Konfigurasi stancl/tenancy & database isolation
+-- database/
¦   +-- migrations/          # 23 migrasi central
¦   ¦   +-- tenant/          # 72 migrasi skema database tenant
¦   +-- seeders/             # Seeder central & TenantDatabaseSeeder
+-- routes/
¦   +-- web.php              # Domain central
¦   +-- tenant.php           # Subdomain tenant (cek-status, cetak nota)
¦   +-- console.php          # Cron: storage cleaner (02:00), auto-renew (00:05), payroll (28 tiap bulan 23:00)
+-- resources/views/         # Blade (nota cetak, komponen tracking, dll.)
```

---

## ? 7. Perintah Artisan Pendukung

```bash
# Migrasi & seeder tenant
php artisan tenants:migrate
php artisan tenants:rollback
php artisan tenants:seed --class=TenantDatabaseSeeder

# Otomasi terjadwal (dijalankan scheduler, bisa ditest manual)
php artisan payroll:auto-generate       # Generate slip gaji bulanan
php artisan subscriptions:auto-renew    # Perpanjang langganan aktif
php artisan storage:clean-orphans       # Hapus file orphan di storage

# Utilitas dev / maintenance
php artisan tenants:artisan "shield:generate --all"  # Sync permissions Shield ke semua tenant
php artisan sync-permissions-to-tenants              # Sinkronisasi permission ke database tenant
```

> [!NOTE]
> Integrasi payment gateway **Midtrans** tersedia sebagai *planned feature* (`MidtransService`). Saat ini sistem menggunakan alur transfer manual dengan verifikasi admin.

---

## ?? 8. Lisensi

Proyek Schienetren dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).



---

## ðŸš€ 9. Panduan Deployment Production

Sebelum menjalankan aplikasi di server live / production, ikuti checklist kesiapan berikut:

### 1. Hardening File `.env` Server
Pastikan parameter berikut diterapkan di file `.env` server production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-utama-anda.com

TENANCY_CENTRAL_DOMAIN=domain-utama-anda.com
TENANCY_CENTRAL_DOMAINS=domain-utama-anda.com

# Wajib true saat menggunakan HTTPS
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
```

> [!WARNING]
> Jangan biarkan `APP_DEBUG=true` di server production karena akan membocorkan stack trace, kredensial database, dan variabel lingkungan saat terjadi exception/error.

### 2. Hak Akses Database (Multi-Tenancy)
Karena sistem membuat database secara dinamis per tenant (`tenant__<id>`), pastikan user MySQL server memiliki hak akses untuk membuat database baru:
```sql
GRANT ALL PRIVILEGES ON `tenant__%`.* TO 'user_mysql'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Konfigurasi Web Server (Nginx)
Arahkan DocumentRoot web server tepat ke subfolder `/public`, **bukan ke root project**:
```nginx
server {
    listen 80;
    server_name domain-utama-anda.com *.domain-utama-anda.com;
    root /var/www/schienetren/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Blokir akses langsung ke file tersembunyi/sensitif
    location ~ /\.(env|git|htaccess) {
        deny all;
    }
}
```

### 4. Perintah Rilis di Server Production
Jalankan urutan perintah berikut di server saat deploy atau update:
```bash
# 1. Pasang dependensi tanpa paket dev
composer install --no-dev --optimize-autoloader

# 2. Migrasi database central
php artisan migrate --force

# 3. Aktifkan cache production untuk performa & keamanan
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components

# 4. Pastikan symlink storage terhubung
php artisan storage:link
```
