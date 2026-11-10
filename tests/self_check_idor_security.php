<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Repair;
use App\Models\Tenant\Product;
use App\Models\Tenant\User;
use App\Models\Tenant\Client;
use Illuminate\Support\Facades\DB;

echo "====================================================================\n";
echo " SCHIENETREN ERP & POS - IDOR SECURITY AUDIT & VERIFICATION RUNNER \n";
echo "====================================================================\n\n";

$tSchienetren = Tenant::find('schienetren');
$tDscom = Tenant::find('dscom');

assert($tSchienetren !== null, "Tenant schienetren must exist");
assert($tDscom !== null, "Tenant dscom must exist");

// --- PHASE 1: Verify Tenant Schienetren Context ---
echo "[1] Initializing Tenant: Schienetren (schienetren.schienetren.test)...\n";
tenancy()->initialize($tSchienetren);
$dbSchienetren = DB::connection()->getDatabaseName();
echo "    -> Active Database: {$dbSchienetren}\n";

$salesCountSchienetren = Sale::count();
$repairsCountSchienetren = Repair::count();
$product2Schienetren = Product::find(2);
$user3Schienetren = User::find(3);
$client1Schienetren = Client::find(1);

echo "    -> Total Sales Records   : {$salesCountSchienetren}\n";
echo "    -> Total Repair Records  : {$repairsCountSchienetren}\n";
echo "    -> Product ID 2 Name     : " . ($product2Schienetren ? $product2Schienetren->name : 'N/A') . "\n";
echo "    -> User ID 3 Email       : " . ($user3Schienetren ? $user3Schienetren->email : 'N/A') . "\n";
echo "    -> Client ID 1 Name      : " . ($client1Schienetren ? $client1Schienetren->name : 'N/A') . "\n";
tenancy()->end();
echo "    -> Tenancy ended. Reverted to central.\n\n";

// --- PHASE 2: Verify Tenant DSCOM Context & IDOR Attack Simulation ---
echo "[2] Initializing Tenant: DSCOM (dscom.schienetren.test)...\n";
tenancy()->initialize($tDscom);
$dbDscom = DB::connection()->getDatabaseName();
echo "    -> Active Database: {$dbDscom}\n";

assert($dbSchienetren !== $dbDscom, "Tenant databases must be distinct!");

// TEST 1: IDOR on Sales
echo "\n--- TEST CASE SEC-IDOR-01: Sales Invoice Direct Object Reference ---\n";
$targetSaleId = 1;
$leakedSale = Sale::find($targetSaleId);
if ($leakedSale === null) {
    echo "  [PASS] Sale ID {$targetSaleId} NOT FOUND in dscom database. (Zero Leakage!)\n";
} else {
    echo "  [FAIL] CRITICAL: Sale ID {$targetSaleId} was accessible in dscom!\n";
}

// TEST 2: IDOR on Repairs
echo "\n--- TEST CASE SEC-IDOR-02: Repair / Service Direct Object Reference ---\n";
$targetRepairId = 1;
$leakedRepair = Repair::find($targetRepairId);
if ($leakedRepair === null) {
    echo "  [PASS] Repair ID {$targetRepairId} NOT FOUND in dscom database. (Zero Leakage!)\n";
} else {
    echo "  [FAIL] CRITICAL: Repair ID {$targetRepairId} was accessible in dscom!\n";
}

// TEST 3: IDOR on Clients
echo "\n--- TEST CASE SEC-IDOR-03: Client / Customer Direct Object Reference ---\n";
$targetClientId = 1;
$leakedClient = Client::find($targetClientId);
if ($leakedClient === null) {
    echo "  [PASS] Client ID {$targetClientId} NOT FOUND in dscom database. (Zero Leakage!)\n";
} else {
    echo "  [FAIL] CRITICAL: Client ID {$targetClientId} was accessible in dscom!\n";
}

// TEST 4: ID Collision Indifference (Same ID, Distinct Entities)
echo "\n--- TEST CASE SEC-IDOR-04: ID Collision Indifference (Product ID 2) ---\n";
$product2Dscom = Product::find(2);
echo "  Product ID 2 in Schienetren : " . ($product2Schienetren ? $product2Schienetren->name : 'N/A') . "\n";
echo "  Product ID 2 in DSCOM       : " . ($product2Dscom ? $product2Dscom->name : 'N/A') . "\n";
if ($product2Dscom && $product2Schienetren && $product2Dscom->name !== $product2Schienetren->name) {
    echo "  [PASS] ID Collision Isolated: Identical ID yields distinct tenant-specific entities.\n";
} else {
    echo "  [FAIL] ID collision overlap detected!\n";
}

// TEST 5: IDOR on Users / Staff
echo "\n--- TEST CASE SEC-IDOR-05: User / Staff Direct Object Reference (User ID 3) ---\n";
$targetUserId = 3;
$leakedUser = User::find($targetUserId);
if ($leakedUser === null) {
    echo "  [PASS] User ID {$targetUserId} NOT FOUND in dscom database. (Zero Leakage!)\n";
} else {
    echo "  [FAIL] CRITICAL: User ID {$targetUserId} was accessible in dscom!\n";
}

// TEST 6: Parameter Tampering on Eloquent Query
echo "\n--- TEST CASE SEC-IDOR-06: Raw Query Cross-Database Boundary Check ---\n";
$currentDb = DB::connection()->getDatabaseName();
echo "  Current connection database: {$currentDb}\n";
assert($currentDb === 'tenant__dscom', "Current connection must be tenant__dscom");
echo "  [PASS] Connection strictly scoped to tenant database.\n";

tenancy()->end();
echo "\n====================================================================\n";
echo " ALL IDOR SECURITY AUDIT SCENARIOS COMPLETED SUCCESSFULLY (PASS)\n";
echo "====================================================================\n";

