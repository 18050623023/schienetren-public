<?php

require 'c:/laragon/www/schienetren/vendor/autoload.php';
$app = require_once 'c:/laragon/www/schienetren/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\TenantFeature;
use App\Filament\Tenant\Pages\Reports\FinanceReportPage;
use App\Filament\Tenant\Pages\Reports\HrReportPage;
use App\Filament\Tenant\Pages\Reports\InventoryReportPage;
use App\Filament\Tenant\Pages\Reports\ServiceReportPage;
use App\Models\Tenant;
use App\Models\Tenant\FeatureToggle;
use App\Models\Tenant\User;
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

echo "Testing Cluster 6 with Tenant ID: {$tenant->id}, User: {$user->email}\n";

// --- TEST 1: Enable ADVANCED_REPORTS ---
FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::ADVANCED_REPORTS->value],
    ['is_enabled' => true]
);
assert($tenant->hasFeatureCode(TenantFeature::ADVANCED_REPORTS) === true, "ADVANCED_REPORTS must be enabled");

assert(FinanceReportPage::canAccess() === true, "FinanceReportPage must be accessible when enabled");
assert(HrReportPage::canAccess() === true, "HrReportPage must be accessible when enabled");
assert(InventoryReportPage::canAccess() === true, "InventoryReportPage must be accessible when enabled");
assert(ServiceReportPage::canAccess() === true, "ServiceReportPage must be accessible when enabled");
echo "[PASS] Positive checks: All 4 report pages canAccess() return TRUE when enabled.\n";

// --- TEST 2: Disable ADVANCED_REPORTS ---
FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::ADVANCED_REPORTS->value],
    ['is_enabled' => false]
);
assert($tenant->hasFeatureCode(TenantFeature::ADVANCED_REPORTS) === false, "ADVANCED_REPORTS must be disabled");

assert(FinanceReportPage::canAccess() === false, "FinanceReportPage must NOT be accessible when disabled");
assert(HrReportPage::canAccess() === false, "HrReportPage must NOT be accessible when disabled");
assert(InventoryReportPage::canAccess() === false, "InventoryReportPage must NOT be accessible when disabled");
assert(ServiceReportPage::canAccess() === false, "ServiceReportPage must NOT be accessible when disabled");
echo "[PASS] Negative checks: All 4 report pages canAccess() return FALSE when disabled.\n";

// --- RESTORE ---
FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::ADVANCED_REPORTS->value],
    ['is_enabled' => true]
);

echo "[SUCCESS] All Cluster 6 assertion checks passed successfully!\n";
