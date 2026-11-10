<?php

require 'c:/laragon/www/schienetren/vendor/autoload.php';
$app = require_once 'c:/laragon/www/schienetren/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\Products\ProductResource;
use App\Filament\Tenant\Resources\Repairs\RepairResource;
use App\Filament\Tenant\Resources\Users\UserResource;
use App\Models\Tenant;
use App\Models\Tenant\FeatureToggle;
use App\Models\Tenant\Product;
use App\Models\Tenant\Repair;
use App\Models\Tenant\User;
use App\Policies\Tenant\ProductPolicy;
use App\Policies\Tenant\RepairPolicy;
use App\Policies\Tenant\UserPolicy;
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

echo "Testing Cluster 7 with Tenant ID: {$tenant->id}, User: {$user->email}\n";

// Helper to set limit
function setTestLimit($code, $val) {
    DB::table('tenant_feature_limits')->updateOrInsert(
        ['feature_code' => $code],
        [
            'feature_type' => 'limit',
            'max_value' => $val,
            'is_expired' => false,
            'updated_at' => now(),
        ]
    );
    Tenant::flushFeatureCache();
}

// Ensure toggles are active
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::MAX_USER->value], ['is_enabled' => true]);
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::MAX_PRODUCT->value], ['is_enabled' => true]);
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::MAX_MONTHLY_REPAIR->value], ['is_enabled' => true]);

// --- TEST 1: NULL = UNLIMITED CHECK ---
echo "--- Testing NULL = Unlimited Behavior ---\n";
// Set limit to null in DB
setTestLimit(TenantFeature::MAX_USER->value, null);
$checkNull = User::checkUserLimit();
assert($checkNull->passed === true, "Null limit must be treated as UNLIMITED and pass");

setTestLimit(TenantFeature::MAX_PRODUCT->value, null);
$checkProdNull = Product::checkProductLimit();
assert($checkProdNull->passed === true, "Null limit for product must be UNLIMITED and pass");

setTestLimit(TenantFeature::MAX_MONTHLY_REPAIR->value, null);
$checkRepairNull = Repair::checkMonthlyRepairLimit();
assert($checkRepairNull->passed === true, "Null limit for repair must be UNLIMITED and pass");
echo "[PASS] Null limit correctly resolves to UNLIMITED across all models.\n";

// --- TEST 2: MAX_USER QUOTA ENFORCEMENT ---
echo "--- Testing MAX_USER Quota ---\n";
$currentUserCount = User::count();
// Set limit higher than current count
setTestLimit(TenantFeature::MAX_USER->value, (string) ($currentUserCount + 10));
assert(User::checkUserLimit()->passed === true, "User check must pass when below limit");

// Set limit lower/equal to current count
setTestLimit(TenantFeature::MAX_USER->value, (string) $currentUserCount);
$userCheckFail = User::checkUserLimit();
assert($userCheckFail->passed === false, "User check must fail when current count >= max limit");
assert(str_contains($userCheckFail->message, 'Batas maksimal pengguna'), "Message must inform user limit exceeded");

$userPolicy = new UserPolicy();
assert($userPolicy->create($user) === false, "UserPolicy::create must return false when quota is reached");
echo "[PASS] MAX_USER correctly blocks creation when quota is reached.\n";

// --- TEST 3: MAX_PRODUCT QUOTA ENFORCEMENT ---
echo "--- Testing MAX_PRODUCT Quota ---\n";
$currentProdCount = Product::count();
setTestLimit(TenantFeature::MAX_PRODUCT->value, (string) ($currentProdCount + 10));
assert(Product::checkProductLimit()->passed === true, "Product check must pass when below limit");

setTestLimit(TenantFeature::MAX_PRODUCT->value, (string) $currentProdCount);
$prodCheckFail = Product::checkProductLimit();
assert($prodCheckFail->passed === false, "Product check must fail when current count >= max limit");

$prodPolicy = new ProductPolicy();
assert($prodPolicy->create($user) === false, "ProductPolicy::create must return false when quota is reached");
echo "[PASS] MAX_PRODUCT correctly blocks creation when quota is reached.\n";

// --- TEST 4: MAX_MONTHLY_REPAIR QUOTA ENFORCEMENT ---
echo "--- Testing MAX_MONTHLY_REPAIR Quota ---\n";
$currentMonthlyRepairs = Repair::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
setTestLimit(TenantFeature::MAX_MONTHLY_REPAIR->value, (string) ($currentMonthlyRepairs + 10));
assert(Repair::checkMonthlyRepairLimit()->passed === true, "Repair check must pass when below limit");

setTestLimit(TenantFeature::MAX_MONTHLY_REPAIR->value, (string) $currentMonthlyRepairs);
$repairCheckFail = Repair::checkMonthlyRepairLimit();
assert($repairCheckFail->passed === false, "Repair check must fail when current count >= max limit");

$repairPolicy = new RepairPolicy();
assert($repairPolicy->create($user) === false, "RepairPolicy::create must return false when quota is reached");
echo "[PASS] MAX_MONTHLY_REPAIR correctly blocks creation when quota is reached.\n";

// Restore standard defaults from PlanFeatureSeeder
setTestLimit(TenantFeature::MAX_USER->value, '20');
setTestLimit(TenantFeature::MAX_PRODUCT->value, '1000');
setTestLimit(TenantFeature::MAX_MONTHLY_REPAIR->value, '500');

echo "[SUCCESS] All Cluster 7 limit & quota assertion checks passed successfully!\n";
