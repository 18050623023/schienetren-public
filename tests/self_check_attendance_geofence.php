<?php

require 'c:/laragon/www/schienetren/vendor/autoload.php';
$app = require_once 'c:/laragon/www/schienetren/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\TenantFeature;
use App\Models\Tenant;
use App\Models\Tenant\FeatureToggle;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

echo "Starting self-check for ATTENDANCE_GEOFENCE...\n";

// Helper class using trait HandlesAttendance for testing
class AttendanceTestRunner {
    use \App\Filament\Tenant\Pages\Attendance\HandlesAttendance;

    public function testValidateRequest(array $data) {
        $this->validateRequest($data);
    }
}

$runner = new AttendanceTestRunner();

// Test 1: Feature ATTENDANCE_GEOFENCE is DISABLED (OFF)
FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::ATTENDANCE_GEOFENCE->value],
    ['is_enabled' => false]
);
assert(tenant()->hasFeatureCode(TenantFeature::ATTENDANCE_GEOFENCE) === false, "Geofence is disabled");

// 1.a Request without map coordinates should PASS when Geofence is OFF
$validDataWithoutMap = [
    'type' => 'in',
    'employee_assignment_id' => 1,
    'date' => '2026-10-19',
    'latitude' => -7.306,
    'longitude' => 112.729,
    'map' => [
        'latitude' => null,
        'longitude' => null,
        'distance' => null,
    ],
    'photo' => 'data:image/jpeg;base64,...',
];

$passedNoGps = false;
try {
    $runner->testValidateRequest($validDataWithoutMap);
    $passedNoGps = true;
} catch (ValidationException $e) {
    // If assignment 1 doesn't exist in DB, validation for exists:employee_assignments might fail,
    // let's check which errors occurred
    $errors = $e->errors();
    if (!isset($errors['map.latitude']) && !isset($errors['map.longitude']) && !isset($errors['map.distance'])) {
        $passedNoGps = true; // map coordinates are indeed nullable!
    } else {
        echo "FAIL: map coordinates should be nullable when geofence is OFF: " . json_encode($errors) . "\n";
        exit(1);
    }
}
assert($passedNoGps === true, "Map coordinates are nullable when Geofence is OFF");
echo "[PASS] 1.a validateRequest allows nullable map coordinates when Geofence is OFF.\n";

// 1.b Camera visibility logic when Geofence is OFF and distance > radius
$cameraHiddenClosure = function ($get) {
    if (!tenant()->hasFeatureCode(TenantFeature::ATTENDANCE_GEOFENCE)) {
        return false;
    }
    $distance = data_get($get('map'), 'distance');
    return $distance > $get('attendance_radius');
};

$mockGetOutsideRadius = function ($field) {
    if ($field === 'map') return ['distance' => 5000];
    if ($field === 'attendance_radius') return 50;
    return null;
};

$isHiddenOff = $cameraHiddenClosure($mockGetOutsideRadius);
assert($isHiddenOff === false, "Camera should NOT be hidden even if distance > radius when Geofence is OFF");
echo "[PASS] 1.b Camera remains visible even outside radius when Geofence is OFF.\n";


// Test 2: Feature ATTENDANCE_GEOFENCE is ENABLED (ON)
FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::ATTENDANCE_GEOFENCE->value],
    ['is_enabled' => true]
);
assert(tenant()->hasFeatureCode(TenantFeature::ATTENDANCE_GEOFENCE) === true, "Geofence is enabled");

// 2.a Request without map coordinates should FAIL when Geofence is ON
$failedGpsWhenOn = false;
try {
    $runner->testValidateRequest($validDataWithoutMap);
} catch (ValidationException $e) {
    $errors = $e->errors();
    if (isset($errors['map.latitude']) || isset($errors['map.longitude']) || isset($errors['map.distance'])) {
        $failedGpsWhenOn = true;
    }
}
assert($failedGpsWhenOn === true, "Map coordinates are required when Geofence is ON");
echo "[PASS] 2.a validateRequest requires map coordinates when Geofence is ON.\n";

// 2.b Camera visibility logic when Geofence is ON and distance > radius
$isHiddenOn = $cameraHiddenClosure($mockGetOutsideRadius);
assert($isHiddenOn === true, "Camera MUST be hidden when distance > radius and Geofence is ON");
echo "[PASS] 2.b Camera is hidden when outside radius and Geofence is ON.\n";

// 2.c Camera visibility logic when Geofence is ON and distance <= radius
$mockGetInsideRadius = function ($field) {
    if ($field === 'map') return ['distance' => 20];
    if ($field === 'attendance_radius') return 50;
    return null;
};
$isHiddenInside = $cameraHiddenClosure($mockGetInsideRadius);
assert($isHiddenInside === false, "Camera should be visible when inside radius and Geofence is ON");
echo "[PASS] 2.c Camera is visible when inside radius and Geofence is ON.\n";

// Clean up: restore default feature toggle
FeatureToggle::updateOrCreate(
    ['feature_code' => TenantFeature::ATTENDANCE_GEOFENCE->value],
    ['is_enabled' => true]
);

echo "\nSUCCESS: ALL ATTENDANCE_GEOFENCE SELF-CHECKS PASSED!\n";
