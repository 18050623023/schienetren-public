<?php

require 'c:/laragon/www/schienetren/vendor/autoload.php';
$app = require_once 'c:/laragon/www/schienetren/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\StockTransfers\StockTransferResource;
use App\Models\Tenant;
use App\Models\Tenant\Branch;
use App\Models\Tenant\FeatureToggle;
use App\Models\Tenant\StockTransfer;
use App\Models\Tenant\User;
use App\Policies\Tenant\StockTransferPolicy;
use Illuminate\Support\Facades\Auth;

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

echo "Testing with Tenant ID: {$tenant->id}, User: {$user->email}\n";
$policy = new StockTransferPolicy();

// Skenario 1: Fitur STOCK_TRANSFER DINONAKTIFKAN
echo "--- Skenario 1: Fitur STOCK_TRANSFER DINONAKTIFKAN ---\n";
FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::STOCK_TRANSFER->value],
    ['is_enabled' => false]
);

assert($tenant->hasFeatureCode(TenantFeature::STOCK_TRANSFER) === false, "Feature must be disabled");
assert(StockTransferResource::canViewAny() === false, "Resource::canViewAny() must be false when feature disabled");
assert(StockTransferResource::canCreate() === false, "Resource::canCreate() must be false when feature disabled");
assert($policy->viewAny($user) === false, "Policy::viewAny() must be false when feature disabled");
assert($policy->create($user) === false, "Policy::create() must be false when feature disabled");

try {
    StockTransfer::create([
        'code' => 'TEST-001',
        'from_branch_id' => 1,
        'to_branch_id' => 2,
        'date' => now(),
    ]);
    assert(false, "StockTransfer::create() must fail when feature is disabled");
} catch (\Exception $e) {
    assert(str_contains($e->getMessage(), 'tidak aktif'), "Exception must mention feature inactive: " . $e->getMessage());
    echo "[PASS] Skenario 1: Pembuatan dan akses mutasi stok diblokir saat fitur nonaktif.\n";
}

// Skenario 2: Fitur DIAKTIFKAN, tetapi Cabang Aktif <= 1
echo "--- Skenario 2: Fitur DIAKTIFKAN, tetapi Cabang Aktif <= 1 ---\n";
FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::STOCK_TRANSFER->value],
    ['is_enabled' => true]
);
assert($tenant->hasFeatureCode(TenantFeature::STOCK_TRANSFER) === true, "Feature must be enabled");

// Amankan state cabang riil
$originalBranches = Branch::all();
// Nonaktifkan cabang selain cabang pertama agar count aktif == 1
$firstBranch = Branch::first();
if (!$firstBranch) {
    $firstBranch = Branch::create(['name' => 'Cabang Utama', 'is_active' => true]);
}
Branch::where('id', '!=', $firstBranch->id)->update(['is_active' => false]);
$firstBranch->update(['is_active' => true]);

assert(Branch::where('is_active', true)->count() === 1, "Active branches count must be 1 for this test");
assert(StockTransferResource::canViewAny() === true, "Resource::canViewAny() must be true to allow viewing past history");
assert(StockTransferResource::canCreate() === false, "Resource::canCreate() must be false when active branch <= 1");
assert($policy->create($user) === false, "Policy::create() must be false when active branch <= 1");

try {
    StockTransfer::create([
        'code' => 'TEST-002',
        'from_branch_id' => $firstBranch->id,
        'to_branch_id' => $firstBranch->id,
        'date' => now(),
    ]);
    assert(false, "StockTransfer::create() must fail when active branch <= 1");
} catch (\Exception $e) {
    assert(str_contains($e->getMessage(), 'minimal 2 cabang aktif'), "Exception must mention min 2 branches: " . $e->getMessage());
    echo "[PASS] Skenario 2: canCreate() dan Model Hook memblokir saat cabang aktif <= 1.\n";
}

// Skenario 3: Fitur DIAKTIFKAN, Cabang Aktif >= 2
echo "--- Skenario 3: Fitur DIAKTIFKAN, Cabang Aktif >= 2 ---\n";
$secondBranch = Branch::where('id', '!=', $firstBranch->id)->first();
if (!$secondBranch) {
    $secondBranch = Branch::create(['name' => 'Cabang Kedua', 'is_active' => true]);
} else {
    $secondBranch->update(['is_active' => true]);
}

assert(Branch::where('is_active', true)->count() >= 2, "Active branches count must be >= 2");
assert(StockTransferResource::canViewAny() === true, "Resource::canViewAny() must be true");
assert(StockTransferResource::canCreate() === true, "Resource::canCreate() must be true when branch >= 2");
assert($policy->create($user) === true, "Policy::create() must allow authorized user when branch >= 2");

// Cek validasi cabang asal dan tujuan sama
try {
    StockTransfer::create([
        'code' => 'TEST-003',
        'from_branch_id' => $firstBranch->id,
        'to_branch_id' => $firstBranch->id,
        'date' => now(),
    ]);
    assert(false, "StockTransfer::create() must fail when from_branch_id == to_branch_id");
} catch (\Exception $e) {
    assert(str_contains($e->getMessage(), 'tidak boleh sama'), "Exception must mention branches cannot be same: " . $e->getMessage());
    echo "[PASS] Skenario 3: Validasi from_branch != to_branch bekerja dengan tepat.\n";
}

// Restore state cabang asli jika ada
foreach ($originalBranches as $ob) {
    Branch::where('id', $ob->id)->update(['is_active' => $ob->is_active]);
}

echo "=== ALL SELF CHECK TESTS PASSED FOR STOCK_TRANSFER ===\n";
