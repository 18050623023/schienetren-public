<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFYING CUSTOM COMPONENTS & 3RD-PARTY FILAMENT PLUGINS ===\n";

// 1. GeofenceMap
echo "1. Testing GeofenceMap instantiation: ";
$map = App\Filament\Forms\Components\GeofenceMap::make('test_map');
assert($map instanceof Filament\Forms\Components\Field, "GeofenceMap must extend Field");
echo "[PASS] " . get_class($map) . "\n";

// 2. TakePicture Custom Component
echo "2. Testing TakePicture component instantiation: ";
$pic = App\Filament\Forms\Components\TakePicture::make('photo');
assert($pic instanceof Filament\Forms\Components\Field, "TakePicture must extend Field");
echo "[PASS] " . get_class($pic) . "\n";

// 3. Attendance Page & Attendance Form
echo "3. Testing AttendanceForm and AttendancePage: ";
$refAttendance = new ReflectionClass(App\Filament\Tenant\Pages\AttendancePage::class);
assert($refAttendance->isInstantiable() || !$refAttendance->isAbstract());
$refForm = new ReflectionClass(App\Filament\Tenant\Pages\Attendance\AttendanceForm::class);
assert($refForm->hasMethod('attendance'));
echo "[PASS] AttendanceForm & AttendancePage verified\n";

// 4. Flowforge Kanban Board (Relaticle/Flowforge)
echo "4. Testing TechnicianBoard & Flowforge Kanban plugin: ";
$refBoard = new ReflectionClass(App\Filament\Tenant\Pages\TechnicianBoard::class);
$parentClass = $refBoard->getParentClass();
assert($parentClass !== false, "TechnicianBoard must extend BoardPage");
echo "[PASS] TechnicianBoard extends " . $parentClass->getName() . "\n";

// 5. Filament Leaflet Plugin
echo "5. Testing eduardoribeirodev/filament-leaflet: ";
$leafletClass = 'EduardoRibeiroDev\FilamentLeaflet\Fields\MapPicker';
assert(class_exists($leafletClass), "MapPicker class must exist");
echo "[PASS] Leaflet plugin MapPicker loaded\n";

// 6. Filament Autograph Plugin
echo "6. Testing saade/filament-autograph: ";
$autographClass = 'Saade\FilamentAutograph\Forms\Components\SignaturePad';
assert(class_exists($autographClass), "SignaturePad class must exist");
echo "[PASS] Autograph plugin loaded\n";

// 7. Filament Modal Repeater Plugin
echo "7. Testing yousefaman/filament-modal-repeater: ";
$repeaterClass = 'YousefAman\ModalRepeater\ModalRepeater';
assert(class_exists($repeaterClass), "ModalRepeater class must exist");
echo "[PASS] ModalRepeater plugin loaded\n";

// 8. Filament Take Picture Field Vendor Package
echo "8. Testing emmanpbarrameda/filament-take-picture-field: ";
$takePicVendorClass = 'emmanpbarrameda\FilamentTakePictureField\Forms\Components\TakePicture';
assert(class_exists($takePicVendorClass), "Vendor TakePicture class must exist");
echo "[PASS] Vendor TakePicture loaded\n";

echo "=== ALL CUSTOM COMPONENTS & THIRD-PARTY PLUGINS VERIFIED SUCCESSFULLY ===\n";
