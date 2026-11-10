<?php

require 'c:/laragon/www/schienetren/vendor/autoload.php';
$app = require_once 'c:/laragon/www/schienetren/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\Stock;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\User;
use App\Services\Stock\StockAdjustmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$tenant = Tenant::first();
if (!$tenant) {
    echo "FAIL: No tenant available for testing.\n";
    exit(1);
}

tenancy()->initialize($tenant);

$user = User::first();
if (!$user) {
    echo "FAIL: No tenant user found.\n";
    exit(1);
}
Auth::setUser($user);

$branch = Branch::where('is_active', true)->first();
if (!$branch) {
    echo "FAIL: No active branch found.\n";
    exit(1);
}

// Cari produk non-SN untuk tes dasar
$product = Product::where('type', 'product')->where('has_sn', false)->first();
if (!$product) {
    // Buat produk sementara untuk tes jika tidak ada
    $product = Product::create([
        'code' => 'TEST-ADJ-PROD',
        'name' => 'Produk Tes Adjust',
        'type' => 'product',
        'has_sn' => false,
        'has_variant' => false,
    ]);
}

$variant = $product->variants()->first();

echo "Testing StockAdjustmentService with Tenant: {$tenant->id}, Branch: {$branch->name}, Product: {$product->name}\n";
$service = app(StockAdjustmentService::class);

// Catat saldo stok awal
$initialStock = Stock::where('branch_id', $branch->id)
    ->where('product_id', $product->id)
    ->value('stock') ?? 0;

echo "Stok awal sebelum tes: {$initialStock}\n";

// --- Skenario 1: Penambahan Stok (In) ---
echo "--- Skenario 1: Penambahan Stok (direction: in) ---\n";
$stockAfterIn = $service->adjust(
    branchId: $branch->id,
    productId: $product->id,
    variantId: $variant?->id,
    direction: 'in',
    quantity: 5,
    reason: 'Pembelian Kas Eceran',
    note: 'Beli 5 pcs di toko mitra'
);

assert($stockAfterIn->stock === ($initialStock + 5), "Stok harus bertambah 5. Aktual: {$stockAfterIn->stock}");

$latestMovement = StockMovement::where('product_id', $product->id)
    ->where('branch_id', $branch->id)
    ->latest('id')
    ->first();

assert($latestMovement !== null, "StockMovement harus tercipta");
assert($latestMovement->direction === 'in', "Direction harus in");
assert($latestMovement->quantity === 5, "Quantity harus 5");
assert($latestMovement->source_type === 'manual_adjustment', "Source type harus manual_adjustment");
assert(str_contains($latestMovement->note, 'Pembelian Kas Eceran'), "Note harus memuat alasan");
echo "[PASS] Skenario 1: Penambahan stok & pencatatan kartu stok berhasil.\n";

// --- Skenario 2: Pengurangan Stok (Out) ---
echo "--- Skenario 2: Pengurangan Stok (direction: out) ---\n";
$stockAfterOut = $service->adjust(
    branchId: $branch->id,
    productId: $product->id,
    variantId: $variant?->id,
    direction: 'out',
    quantity: 2,
    reason: 'Barang Rusak / Pecah',
    note: 'Jatuh saat pemindahan rak'
);

assert($stockAfterOut->stock === ($initialStock + 3), "Stok harus berkurang 2 menjadi " . ($initialStock + 3) . ". Aktual: {$stockAfterOut->stock}");

$outMovement = StockMovement::where('product_id', $product->id)
    ->where('branch_id', $branch->id)
    ->latest('id')
    ->first();

assert($outMovement->direction === 'out', "Direction harus out");
assert($outMovement->quantity === 2, "Quantity harus 2");
assert(str_contains($outMovement->note, 'Barang Rusak / Pecah'), "Note harus memuat alasan rusak");
echo "[PASS] Skenario 2: Pengurangan stok & kartu stok berhasil.\n";

// --- Skenario 3: Over-reduction Guard (Pengurangan Melebihi Stok) ---
echo "--- Skenario 3: Over-reduction Guard ---\n";
try {
    $current = $stockAfterOut->stock;
    $service->adjust(
        branchId: $branch->id,
        productId: $product->id,
        variantId: $variant?->id,
        direction: 'out',
        quantity: $current + 10,
        reason: 'Barang Hilang / Selisih'
    );
    assert(false, "Penyesuaian melebihi stok harus gagal dan melempar Exception");
} catch (\Exception $e) {
    assert(str_contains($e->getMessage(), 'tidak mencukupi'), "Pesan error harus mengenai stok tidak cukup: " . $e->getMessage());
    echo "[PASS] Skenario 3: Over-reduction berhasil dicegah.\n";
}

// --- Skenario 4: Produk dengan Serial Number (has_sn = true) ---
echo "--- Skenario 4: Produk dengan Serial Number ---\n";
$snProduct = Product::where('type', 'product')->where('has_sn', true)->first();
if ($snProduct) {
    $snVariant = $snProduct->variants()->first();
    $sn1 = 'TEST-SN-' . uniqid();
    
    // Tambah 1 unit dengan SN baru
    $snStockIn = $service->adjust(
        branchId: $branch->id,
        productId: $snProduct->id,
        variantId: $snVariant?->id,
        direction: 'in',
        quantity: 1,
        reason: 'Pembelian Kas Eceran',
        note: 'Unit masuk dengan serial',
        selectedSerialIds: [],
        newSerialNumbers: [$sn1]
    );

    $createdSerial = ProductSerial::where('serial_number', $sn1)->first();
    assert($createdSerial !== null, "ProductSerial {$sn1} harus terdaftar");
    assert($createdSerial->status === 'available', "Status serial baru harus available");

    // Kurangi 1 unit dengan SN tersebut (karena rusak)
    $snStockOut = $service->adjust(
        branchId: $branch->id,
        productId: $snProduct->id,
        variantId: $snVariant?->id,
        direction: 'out',
        quantity: 1,
        reason: 'Barang Rusak / Pecah',
        note: 'Unit rusak',
        selectedSerialIds: [$createdSerial->id]
    );

    $createdSerial->refresh();
    assert($createdSerial->status === 'damaged', "Status serial harus berubah menjadi damaged");
    echo "[PASS] Skenario 4: Penanganan nomor seri in & out berhasil sempurna.\n";
} else {
    echo "[SKIP] Skenario 4: Tidak ada produk ber-serial untuk diuji.\n";
}

// Cleanup perubahan tes produk non-SN agar saldo stok kembali semula
$service->adjust(
    branchId: $branch->id,
    productId: $product->id,
    variantId: $variant?->id,
    direction: 'out',
    quantity: 3,
    reason: 'Koreksi Fisik Kurang',
    note: 'Cleanup unit test'
);

$finalStock = Stock::where('branch_id', $branch->id)->where('product_id', $product->id)->value('stock');
assert($finalStock === $initialStock, "Stok setelah cleanup harus kembali ke awal ({$initialStock}). Aktual: {$finalStock}");

echo "=== ALL STOCK ADJUSTMENT TESTS PASSED! ===\n";
