<?php

require 'c:/laragon/www/schienetren/vendor/autoload.php';
$app = require_once 'c:/laragon/www/schienetren/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Filament\Tenant\Resources\GoodsReceipts\GoodsReceiptsResource;
use App\Filament\Tenant\Resources\PurchaseOrders\RelationManagers\GoodsReceiptsRelationManager;
use App\Models\Tenant;
use App\Models\Tenant\FeatureToggle;
use App\Models\Tenant\PurchaseOrder;
use App\Models\Tenant\GoodsReceipts;
use App\Models\Tenant\User;
use App\Policies\Tenant\PurchaseOrderPolicy;
use App\Policies\Tenant\GoodsReceiptsPolicy;
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

echo "Testing Purchase Order Gating with Tenant ID: {$tenant->id}, User: {$user->email}\n";
$poPolicy = new PurchaseOrderPolicy();
$grPolicy = new GoodsReceiptsPolicy();

// Catat status awal toggle
$initialToggle = FeatureToggle::where('feature_code', TenantFeature::PURCHASE_ORDER->value)->value('is_enabled');

try {
    // Skenario 1: Fitur PURCHASE_ORDER DINONAKTIFKAN
    echo "--- Skenario 1: Fitur PURCHASE_ORDER DINONAKTIFKAN ---\n";
    FeatureToggle::updateOrCreate(
        ['feature_code' => TenantFeature::PURCHASE_ORDER->value],
        ['is_enabled' => false]
    );

    assert($tenant->hasFeatureCode(TenantFeature::PURCHASE_ORDER) === false, "Feature must be disabled");
    assert(PurchaseOrderResource::canViewAny() === false, "PurchaseOrderResource::canViewAny() must be false when feature disabled");
    assert(PurchaseOrderResource::canCreate() === false, "PurchaseOrderResource::canCreate() must be false when feature disabled");
    assert(GoodsReceiptsResource::canViewAny() === false, "GoodsReceiptsResource::canViewAny() must be false when feature disabled");
    assert(GoodsReceiptsResource::canCreate() === false, "GoodsReceiptsResource::canCreate() must be false when feature disabled");
    assert(GoodsReceiptsRelationManager::canViewForRecord(new PurchaseOrder(), '') === false, "GoodsReceiptsRelationManager::canViewForRecord must be false when feature disabled");
    
    assert($poPolicy->viewAny($user) === false, "PurchaseOrderPolicy::viewAny() must be false when feature disabled");
    assert($poPolicy->create($user) === false, "PurchaseOrderPolicy::create() must be false when feature disabled");
    assert($grPolicy->viewAny($user) === false, "GoodsReceiptsPolicy::viewAny() must be false when feature disabled");
    assert($grPolicy->create($user) === false, "GoodsReceiptsPolicy::create() must be false when feature disabled");

    try {
        PurchaseOrder::create([
            'code' => 'TEST-PO-GATED',
            'branch_id' => 1,
            'date' => now(),
            'status' => 'draft',
        ]);
        assert(false, "PurchaseOrder::create() must fail when feature is disabled");
    } catch (\Exception $e) {
        assert(str_contains($e->getMessage(), 'tidak aktif'), "Exception must mention feature inactive: " . $e->getMessage());
        echo "[PASS] PurchaseOrder creation correctly blocked by model hook.\n";
    }

    try {
        GoodsReceipts::create([
            'code' => 'TEST-GR-GATED',
            'date' => now(),
            'status' => 'draft',
        ]);
        assert(false, "GoodsReceipts::create() must fail when feature is disabled");
    } catch (\Exception $e) {
        assert(str_contains($e->getMessage(), 'tidak aktif'), "Exception must mention feature inactive: " . $e->getMessage());
        echo "[PASS] GoodsReceipts creation correctly blocked by model hook.\n";
    }

    echo "[PASS] Skenario 1 Sukses: Akses dan mutasi PO/GR diblokir sempurna saat fitur nonaktif.\n";

    // Skenario 2: Fitur PURCHASE_ORDER DIAKTIFKAN KEMBALI
    echo "--- Skenario 2: Fitur PURCHASE_ORDER DIAKTIFKAN KEMBALI ---\n";
    FeatureToggle::updateOrCreate(
        ['feature_code' => TenantFeature::PURCHASE_ORDER->value],
        ['is_enabled' => true]
    );

    assert($tenant->hasFeatureCode(TenantFeature::PURCHASE_ORDER) === true, "Feature must be enabled");
    assert(PurchaseOrderResource::canViewAny() === true, "PurchaseOrderResource::canViewAny() must be true when feature enabled");
    assert(PurchaseOrderResource::canCreate() === true, "PurchaseOrderResource::canCreate() must be true when feature enabled");
    assert(GoodsReceiptsResource::canViewAny() === true, "GoodsReceiptsResource::canViewAny() must be true when feature enabled");
    assert(GoodsReceiptsResource::canCreate() === true, "GoodsReceiptsResource::canCreate() must be true when feature enabled");
    assert(GoodsReceiptsRelationManager::canViewForRecord(new PurchaseOrder(), '') === true, "GoodsReceiptsRelationManager::canViewForRecord must be true when feature enabled");

    assert($poPolicy->viewAny($user) === true, "PurchaseOrderPolicy::viewAny() must be true when feature enabled");
    assert($poPolicy->create($user) === true, "PurchaseOrderPolicy::create() must be true when feature enabled");
    assert($grPolicy->viewAny($user) === true, "GoodsReceiptsPolicy::viewAny() must be true when feature enabled");
    assert($grPolicy->create($user) === true, "GoodsReceiptsPolicy::create() must be true when feature enabled");

    echo "[PASS] Skenario 2 Sukses: Akses dan otorisasi PO/GR terbuka kembali saat fitur aktif.\n";

} finally {
    // Kembalikan ke status semula
    FeatureToggle::updateOrCreate(
        ['feature_code' => TenantFeature::PURCHASE_ORDER->value],
        ['is_enabled' => $initialToggle ?? true]
    );
    echo "Cleanup: Toggle PURCHASE_ORDER dikembalikan ke status semula (" . var_export($initialToggle ?? true, true) . ").\n";
}

echo "=== ALL PURCHASE ORDER GATING TESTS PASSED! ===\n";
