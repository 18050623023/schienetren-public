<?php

require 'c:/laragon/www/schienetren/vendor/autoload.php';
$app = require_once 'c:/laragon/www/schienetren/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\TenantFeature;
use App\Filament\Tenant\Pages\PosPage;
use App\Filament\Tenant\Resources\Sales\SaleResource;
use App\Models\Tenant;
use App\Models\Tenant\Sale;
use App\Models\Tenant\User;
use App\Policies\Tenant\SalePolicy;
use App\Services\Pos\PosCheckoutService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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

// Test 1: Policy Resolution
$policy = Gate::getPolicyFor(Sale::class);
assert($policy instanceof SalePolicy, "Gate must resolve Sale::class to SalePolicy");
echo "[PASS] Gate automatically resolves Sale::class to SalePolicy.\n";

// Test 2: Enable POS and verify
\App\Models\Tenant\FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::POS->value],
    ['is_enabled' => true]
);
assert($tenant->hasFeatureCode(TenantFeature::POS) === true, "POS must be enabled");

assert(PosPage::canAccess() === true, "PosPage::canAccess() must return true when POS is enabled for authorized user");
assert(SaleResource::canViewAny() === true, "SaleResource::canViewAny() must return true when POS is enabled");

$policyInstance = new SalePolicy();
assert($policyInstance->create($user) === true, "SalePolicy::create() must allow authorized user when POS is enabled");
assert($policyInstance->viewAny($user) === true, "SalePolicy::viewAny() must allow authorized user when POS is enabled");

$checkoutService = new PosCheckoutService();
try {
    $checkoutService->checkout(['branch_id' => 1, 'items' => []]);
    assert(false, "Should throw exception on empty cart, not succeed");
} catch (\Exception $e) {
    assert($e->getMessage() === 'Keranjang belanja masih kosong.', "Exception must be empty cart, NOT disabled POS: " . $e->getMessage());
}
echo "[PASS] Positive checks passed (POS enabled).\n";

// Test 3: Disable POS and verify gating
\App\Models\Tenant\FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::POS->value],
    ['is_enabled' => false]
);
assert($tenant->hasFeatureCode(TenantFeature::POS) === false, "POS must be disabled");

assert(PosPage::canAccess() === false, "PosPage::canAccess() must return false when POS is disabled");
assert(SaleResource::canViewAny() === false, "SaleResource::canViewAny() must return false when POS is disabled");

assert($policyInstance->create($user) === false, "SalePolicy::create() must deny when POS is disabled");
assert($policyInstance->viewAny($user) === false, "SalePolicy::viewAny() must deny when POS is disabled");

try {
    $checkoutService->checkout([]);
    assert(false, "Checkout must throw exception when POS is disabled");
} catch (\Exception $e) {
    assert(str_contains($e->getMessage(), 'Fitur Point of Sale (POS) tidak aktif.'), "Exception message must indicate POS is inactive. Got: " . $e->getMessage());
}
echo "[PASS] Negative checks passed (POS disabled).\n";

// Restore state
\App\Models\Tenant\FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::POS->value],
    ['is_enabled' => true]
);
echo "[SUCCESS] All POS Feature Gating assertion checks passed successfully!\n";
