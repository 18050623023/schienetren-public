<?php

require 'c:/laragon/www/schienetren/vendor/autoload.php';
$app = require_once 'c:/laragon/www/schienetren/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\StockOpnames\StockOpnameResource;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\AuditStockOpname;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Actions\ActionStatus;
use App\Models\Tenant;
use App\Models\Tenant\FeatureToggle;
use App\Models\Tenant\StockOpname;
use App\Models\Tenant\Stock;
use App\Models\Tenant\Branch;
use App\Models\Tenant\User;
use App\Policies\Tenant\StockOpnamePolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

$tenant = Tenant::first();
if (!$tenant) {
    echo "FAIL: No tenant available for testing.\n";
    exit(1);
}

tenancy()->initialize($tenant);
\Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('tenant'));

$user = User::first();
if (!$user) {
    echo "FAIL: No tenant user found.\n";
    exit(1);
}
Auth::setUser($user);

$branch = Branch::first();
if (!$branch) {
    echo "FAIL: No branch found.\n";
    exit(1);
}

echo "Testing Stock Opname Gating with Tenant: {$tenant->id}, User: {$user->email}, Branch: {$branch->name}\n";
$policy = new StockOpnamePolicy();

// Catat status awal toggle
$initialToggle = FeatureToggle::where('feature_code', TenantFeature::STOCK_OPNAME->value)->value('is_enabled');

try {
    // =========================================================================
    // SKENARIO 1: Fitur STOCK_OPNAME DINONAKTIFKAN (Paket Basic)
    // =========================================================================
    echo "\n--- Skenario 1: Fitur STOCK_OPNAME DINONAKTIFKAN ---\n";
    FeatureToggle::updateOrCreate(
        ['feature_code' => TenantFeature::STOCK_OPNAME->value],
        ['is_enabled' => false]
    );

    assert($tenant->hasFeatureCode(TenantFeature::STOCK_OPNAME) === false, "Feature STOCK_OPNAME must be disabled");

    // 1. Navigation Label should be 'Initial Stock'
    assert(StockOpnameResource::getNavigationLabel() === 'Initial Stock', "Navigation label must be 'Initial Stock' when STOCK_OPNAME disabled");
    echo "  [PASS] Navigation label is dynamically 'Initial Stock'\n";

    // 2. Audit Page Access should be forbidden
    assert(AuditStockOpname::canAccess() === false, "AuditStockOpname::canAccess() must be false when feature disabled");
    echo "  [PASS] AuditStockOpname::canAccess() is false\n";

    // 3. Sub-navigation: Audit menu should be hidden
    $subNav = StockOpnameResource::getSubNavigation();
    $auditSubNav = collect($subNav)->first(fn ($item) => $item->getLabel() === 'Stock Opname (Audit)');
    assert($auditSubNav->isVisible() === false, "SubNav 'Stock Opname (Audit)' must be hidden when feature disabled");
    echo "  [PASS] SubNav 'Stock Opname (Audit)' is hidden\n";

    // 4. Model creating regular opname should throw exception
    $modelThrew = false;
    try {
        StockOpname::create([
            'branch_id' => $branch->id,
            'reference' => 'TEST-REG-' . uniqid(),
            'date' => today(),
            'type' => 'regular',
            'status' => 'draft',
        ]);
    } catch (\Throwable $e) {
        $modelThrew = true;
    }
    assert($modelThrew === true, "Creating regular StockOpname must throw exception when feature disabled");
    echo "  [PASS] StockOpname::create(type: 'regular') blocked by Model Hook\n";

    // 5. Policies for regular opname should return false
    $dummyRegular = new StockOpname([
        'branch_id' => $branch->id,
        'type' => 'regular',
        'status' => 'draft',
        'created_by' => $user->id,
    ]);
    assert($policy->view($user, $dummyRegular) === false, "StockOpnamePolicy::view must be false for regular opname when disabled");
    assert($policy->update($user, $dummyRegular) === false, "StockOpnamePolicy::update must be false for regular opname when disabled");
    assert($policy->delete($user, $dummyRegular) === false, "StockOpnamePolicy::delete must be false for regular opname when disabled");
    assert($policy->forceDeleteAny($user) === false, "StockOpnamePolicy::forceDeleteAny must be false when disabled");
    echo "  [PASS] StockOpnamePolicy rejects view/update/delete for regular opname\n";

    // 6. ActionStatus::initialStockPost for regular opname should abort 403
    $postThrew403 = false;
    try {
        ActionStatus::initialStockPost($dummyRegular);
    } catch (HttpException $e) {
        if ($e->getStatusCode() === 403) {
            $postThrew403 = true;
        }
    }
    assert($postThrew403 === true, "ActionStatus::initialStockPost must abort 403 on regular opname when disabled");
    echo "  [PASS] ActionStatus::initialStockPost aborts 403 on regular opname\n";

    // 7. ActionStatus::stockAuditCancel should abort 403
    $cancelThrew403 = false;
    try {
        ActionStatus::stockAuditCancel($dummyRegular);
    } catch (HttpException $e) {
        if ($e->getStatusCode() === 403) {
            $cancelThrew403 = true;
        }
    }
    assert($cancelThrew403 === true, "ActionStatus::stockAuditCancel must abort 403 on regular opname when disabled");
    echo "  [PASS] ActionStatus::stockAuditCancel aborts 403 on regular opname\n";

    // 8. Auto-hide initial stock when stock > 0
    session(['current_branch_id' => $branch->id]);
    $hasExistingStock = Stock::where('branch_id', $branch->id)->where('stock', '>', 0)->exists();
    if ($hasExistingStock) {
        assert(StockOpnameResource::canCreate() === false, "StockOpnameResource::canCreate() must be false when branch stock > 0");
        $initialSubNav = collect($subNav)->first(fn ($item) => $item->getLabel() === 'Initial Stock (Cabang Baru)');
        assert($initialSubNav->isVisible() === false, "Initial Stock subnav must be hidden when branch stock > 0");
        echo "  [PASS] Initial Stock creation and sub-navigation are hidden when branch stock > 0\n";
    }

    // =========================================================================
    // SKENARIO 2: Fitur STOCK_OPNAME DIAKTIFKAN (Paket Enterprise)
    // =========================================================================
    echo "\n--- Skenario 2: Fitur STOCK_OPNAME DIAKTIFKAN ---\n";
    FeatureToggle::updateOrCreate(
        ['feature_code' => TenantFeature::STOCK_OPNAME->value],
        ['is_enabled' => true]
    );

    assert($tenant->hasFeatureCode(TenantFeature::STOCK_OPNAME) === true, "Feature STOCK_OPNAME must be enabled");

    // 1. Navigation Label should be 'Stock Opname'
    assert(StockOpnameResource::getNavigationLabel() === 'Stock Opname', "Navigation label must be 'Stock Opname' when STOCK_OPNAME enabled");
    echo "  [PASS] Navigation label is 'Stock Opname'\n";

    // 2. Audit Page Access should be permitted
    assert(AuditStockOpname::canAccess() === true, "AuditStockOpname::canAccess() must be true when feature enabled");
    echo "  [PASS] AuditStockOpname::canAccess() is true\n";

    // 3. Sub-navigation: Audit menu should be visible
    $subNavEnabled = StockOpnameResource::getSubNavigation();
    $auditSubNavEnabled = collect($subNavEnabled)->first(fn ($item) => $item->getLabel() === 'Stock Opname (Audit)');
    assert($auditSubNavEnabled->isVisible() === true, "SubNav 'Stock Opname (Audit)' must be visible when feature enabled");
    echo "  [PASS] SubNav 'Stock Opname (Audit)' is visible\n";

    // 4. Model creating regular opname should succeed
    $opnameRecord = StockOpname::create([
        'branch_id' => $branch->id,
        'reference' => 'TEST-AUDIT-' . uniqid(),
        'date' => today(),
        'type' => 'regular',
        'status' => 'draft',
    ]);
    assert($opnameRecord->exists, "Creating regular StockOpname must succeed when feature enabled");
    echo "  [PASS] StockOpname::create(type: 'regular') succeeded: ID {$opnameRecord->id}\n";

    // 5. Policies for regular opname should allow access
    assert($policy->view($user, $opnameRecord) === true, "StockOpnamePolicy::view must allow owner when enabled");
    assert($policy->update($user, $opnameRecord) === true, "StockOpnamePolicy::update must allow owner when enabled");
    echo "  [PASS] StockOpnamePolicy allows view/update for regular opname\n";

    // Clean up created test record
    DB::table('stock_opnames')->where('id', $opnameRecord->id)->delete();
    echo "  [PASS] Cleaned up temporary test record\n";

    echo "\n=== ALL STOCK OPNAME GATING TESTS PASSED! ===\n";

} finally {
    // Kembalikan status toggle ke nilai awal
    if ($initialToggle !== null) {
        FeatureToggle::updateOrCreate(
            ['feature_code' => TenantFeature::STOCK_OPNAME->value],
            ['is_enabled' => $initialToggle]
        );
    } else {
        FeatureToggle::where('feature_code', TenantFeature::STOCK_OPNAME->value)->delete();
    }
    echo "Restored original STOCK_OPNAME toggle state: " . ($initialToggle ? "ENABLED" : "DISABLED") . "\n";
}
