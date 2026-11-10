<?php

require 'c:/laragon/www/schienetren/vendor/autoload.php';
$app = require_once 'c:/laragon/www/schienetren/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\TenantFeature;
use App\Filament\Tenant\Pages\TechnicianBoard;
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

echo "Testing Cluster 5 with Tenant ID: {$tenant->id}, User: {$user->email}\n";

// --- TEST 1: KANBAN_TECHNICIAN ---

// 1.a Enable KANBAN_TECHNICIAN
FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::KANBAN_TECHNICIAN->value],
    ['is_enabled' => true]
);
assert($tenant->hasFeatureCode(TenantFeature::KANBAN_TECHNICIAN) === true, "KANBAN_TECHNICIAN must be enabled");
assert(TechnicianBoard::canAccess() === true, "TechnicianBoard::canAccess() must return true when enabled for authorized user");
echo "[PASS] TechnicianBoard canAccess returns true when KANBAN_TECHNICIAN is enabled.\n";

// 1.b Disable KANBAN_TECHNICIAN
FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::KANBAN_TECHNICIAN->value],
    ['is_enabled' => false]
);
assert($tenant->hasFeatureCode(TenantFeature::KANBAN_TECHNICIAN) === false, "KANBAN_TECHNICIAN must be disabled");
assert(TechnicianBoard::canAccess() === false, "TechnicianBoard::canAccess() must return false when disabled");
echo "[PASS] TechnicianBoard canAccess returns false when KANBAN_TECHNICIAN is disabled.\n";

// Restore KANBAN_TECHNICIAN
FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::KANBAN_TECHNICIAN->value],
    ['is_enabled' => true]
);

echo "[SUCCESS] All Cluster 5 assertion checks passed successfully!\n";
