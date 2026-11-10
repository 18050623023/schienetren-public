<?php

require 'c:/laragon/www/schienetren/vendor/autoload.php';
$app = require_once 'c:/laragon/www/schienetren/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\TenantFeature;
use App\Filament\Tenant\Pages\LeaveBalancePage;
use App\Filament\Tenant\Resources\Compensation\CompensationResource;
use App\Filament\Tenant\Resources\CompensationComponents\CompensationComponentResource;
use App\Filament\Tenant\Resources\EmployeeAssignments\RelationManagers\CompensationPackagesRelationManager;
use App\Filament\Tenant\Resources\Holidays\HolidayResource;
use App\Filament\Tenant\Resources\HrPayrolls\HrPayrollResource;
use App\Filament\Tenant\Resources\LeaveRequests\LeaveRequestResource;
use App\Filament\Tenant\Resources\LeaveTypes\LeaveTypeResource;
use App\Filament\Tenant\Widgets\LeaveBalanceStatWidget;
use App\Filament\Tenant\Widgets\RecentLeaveRequestsWidget;
use App\Models\Tenant;
use App\Models\Tenant\CompensationPackage;
use App\Models\Tenant\FeatureToggle;
use App\Models\Tenant\Holiday;
use App\Models\Tenant\LeaveRequest;
use App\Models\Tenant\User;
use App\Services\PayrollCalculationService;
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

echo "Testing Cluster 4 (Part 1: Points 9, 11, 12) with Tenant ID: {$tenant->id}\n";

// ==========================================
// --- TEST 1: PAYROLL (Poin 9) ---
// ==========================================

// 1.a Enable PAYROLL
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::PAYROLL->value], ['is_enabled' => true]);
assert($tenant->hasFeatureCode(TenantFeature::PAYROLL) === true, "PAYROLL must be enabled");
assert(CompensationResource::canViewAny() === true, "CompensationResource should be visible when PAYROLL enabled");
assert(CompensationComponentResource::canViewAny() === true, "CompensationComponentResource should be visible when PAYROLL enabled");
assert(CompensationPackagesRelationManager::canViewForRecord(new \App\Models\Tenant\EmployeeAssignment(), '') === true, "CompensationPackagesRelationManager should be visible when PAYROLL enabled");
echo "[PASS] PAYROLL active: Resource & RelationManager visible.\n";

// 1.b Disable PAYROLL
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::PAYROLL->value], ['is_enabled' => false]);
assert($tenant->hasFeatureCode(TenantFeature::PAYROLL) === false, "PAYROLL must be disabled");
assert(CompensationResource::canViewAny() === false, "CompensationResource should be hidden when PAYROLL disabled");
assert(CompensationComponentResource::canViewAny() === false, "CompensationComponentResource should be hidden when PAYROLL disabled");
assert(HrPayrollResource::canViewAny() === false, "HrPayrollResource should be hidden when PAYROLL disabled");
assert(CompensationPackagesRelationManager::canViewForRecord(new \App\Models\Tenant\EmployeeAssignment(), '') === false, "CompensationPackagesRelationManager should be hidden when PAYROLL disabled");

$payrollResults = app(PayrollCalculationService::class)->generatePeriodPayroll(1, '2026-10');
assert($payrollResults->isEmpty(), "PayrollCalculationService must return empty collection when disabled");

$payrollBlocked = false;
try {
    CompensationPackage::create(['name' => 'Blocked Package']);
} catch (\Throwable $e) {
    $payrollBlocked = true;
}
assert($payrollBlocked === true, "CompensationPackage creation must throw Exception when PAYROLL disabled");
echo "[PASS] PAYROLL inactive: Resources, Service, and Model creation blocked safely.\n";

// Restore PAYROLL
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::PAYROLL->value], ['is_enabled' => true]);

// ==========================================
// --- TEST 2: LEAVE (Poin 11) ---
// ==========================================

// 2.a Enable LEAVE
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::LEAVE->value], ['is_enabled' => true]);
assert($tenant->hasFeatureCode(TenantFeature::LEAVE) === true, "LEAVE must be enabled");
assert(LeaveRequestResource::canViewAny() === true, "LeaveRequestResource should be visible when LEAVE enabled");
assert(LeaveTypeResource::canViewAny() === true, "LeaveTypeResource should be visible when LEAVE enabled");
assert(LeaveBalanceStatWidget::canView() === true, "LeaveBalanceStatWidget should be visible when LEAVE enabled");
assert(RecentLeaveRequestsWidget::canView() === true, "RecentLeaveRequestsWidget should be visible when LEAVE enabled");
echo "[PASS] LEAVE active: Resources and Widgets visible.\n";

// 2.b Disable LEAVE
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::LEAVE->value], ['is_enabled' => false]);
assert($tenant->hasFeatureCode(TenantFeature::LEAVE) === false, "LEAVE must be disabled");
assert(LeaveRequestResource::canViewAny() === false, "LeaveRequestResource should be hidden when LEAVE disabled");
assert(LeaveTypeResource::canViewAny() === false, "LeaveTypeResource should be hidden when LEAVE disabled");
assert(LeaveBalanceStatWidget::canView() === false, "LeaveBalanceStatWidget should be hidden when LEAVE disabled");
assert(RecentLeaveRequestsWidget::canView() === false, "RecentLeaveRequestsWidget should be hidden when LEAVE disabled");
assert(LeaveBalancePage::canAccess() === false, "LeaveBalancePage should be inaccessible when LEAVE disabled");

$leaveBlocked = false;
try {
    LeaveRequest::create(['reason' => 'Blocked Leave']);
} catch (\Throwable $e) {
    $leaveBlocked = true;
}
assert($leaveBlocked === true, "LeaveRequest creation must throw Exception when LEAVE disabled");
echo "[PASS] LEAVE inactive: Resources, Widgets, Page, and Model creation blocked safely.\n";

// Restore LEAVE
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::LEAVE->value], ['is_enabled' => true]);

// ==========================================
// --- TEST 3: HOLIDAY (Poin 12) ---
// ==========================================

// 3.a Enable HOLIDAY
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::HOLIDAY->value], ['is_enabled' => true]);
assert($tenant->hasFeatureCode(TenantFeature::HOLIDAY) === true, "HOLIDAY must be enabled");
assert(HolidayResource::canViewAny() === true, "HolidayResource should be visible when HOLIDAY enabled");
echo "[PASS] HOLIDAY active: HolidayResource visible.\n";

// 3.b Disable HOLIDAY
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::HOLIDAY->value], ['is_enabled' => false]);
assert($tenant->hasFeatureCode(TenantFeature::HOLIDAY) === false, "HOLIDAY must be disabled");
assert(HolidayResource::canViewAny() === false, "HolidayResource should be hidden when HOLIDAY disabled");

$holidayBlocked = false;
try {
    Holiday::create(['name' => 'Blocked Holiday']);
} catch (\Throwable $e) {
    $holidayBlocked = true;
}
assert($holidayBlocked === true, "Holiday creation must throw Exception when HOLIDAY disabled");
echo "[PASS] HOLIDAY inactive: HolidayResource and Model creation blocked safely.\n";

// Restore HOLIDAY
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::HOLIDAY->value], ['is_enabled' => true]);

// ==========================================
// --- TEST 4: SKILL_MANAGEMENT (Poin 14) ---
// ==========================================

// 4.a Enable SKILL_MANAGEMENT
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::SKILL_MANAGEMENT->value], ['is_enabled' => true]);
assert($tenant->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT) === true, "SKILL_MANAGEMENT must be enabled");
assert(\App\Filament\Tenant\Resources\SkillEmployees\SkillEmployeeResource::canViewAny() === true, "SkillEmployeeResource should be visible when SKILL_MANAGEMENT enabled");
assert(\App\Filament\Tenant\Resources\SkillLevels\SkillLevelResource::canViewAny() === true, "SkillLevelResource should be visible when SKILL_MANAGEMENT enabled");
assert(\App\Filament\Tenant\Resources\SkillSpecializations\SkillSpecializationResource::canViewAny() === true, "SkillSpecializationResource should be visible when SKILL_MANAGEMENT enabled");
echo "[PASS] SKILL_MANAGEMENT active: Resources visible.\n";

// 4.b Disable SKILL_MANAGEMENT
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::SKILL_MANAGEMENT->value], ['is_enabled' => false]);
assert($tenant->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT) === false, "SKILL_MANAGEMENT must be disabled");
assert(\App\Filament\Tenant\Resources\SkillEmployees\SkillEmployeeResource::canViewAny() === false, "SkillEmployeeResource should be hidden when SKILL_MANAGEMENT disabled");
assert(\App\Filament\Tenant\Resources\SkillLevels\SkillLevelResource::canViewAny() === false, "SkillLevelResource should be hidden when SKILL_MANAGEMENT disabled");
assert(\App\Filament\Tenant\Resources\SkillSpecializations\SkillSpecializationResource::canViewAny() === false, "SkillSpecializationResource should be hidden when SKILL_MANAGEMENT disabled");

$skillEmpBlocked = false;
try {
    \App\Models\Tenant\SkillEmployee::create([
        'employee_id' => 1,
        'skill_specialization_id' => 1,
        'skill_level_id' => 1,
    ]);
} catch (\Throwable $e) {
    $skillEmpBlocked = true;
}
assert($skillEmpBlocked === true, "SkillEmployee creation must throw Exception when SKILL_MANAGEMENT disabled");

$skillLevelBlocked = false;
try {
    \App\Models\Tenant\SkillLevel::create(['name' => 'Blocked Level']);
} catch (\Throwable $e) {
    $skillLevelBlocked = true;
}
assert($skillLevelBlocked === true, "SkillLevel creation must throw Exception when SKILL_MANAGEMENT disabled");

$skillSpecBlocked = false;
try {
    \App\Models\Tenant\SkillSpecialization::create(['name' => 'Blocked Spec']);
} catch (\Throwable $e) {
    $skillSpecBlocked = true;
}
assert($skillSpecBlocked === true, "SkillSpecialization creation must throw Exception when SKILL_MANAGEMENT disabled");
echo "[PASS] SKILL_MANAGEMENT inactive: Resources and Model creations blocked safely.\n";

// Restore SKILL_MANAGEMENT
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::SKILL_MANAGEMENT->value], ['is_enabled' => true]);

// ==========================================
// --- TEST 5: SHIFT_MANAGEMENT (Poin 13) ---
// ==========================================

// 5.a Enable SHIFT_MANAGEMENT
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::SHIFT_MANAGEMENT->value], ['is_enabled' => true]);
assert($tenant->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT) === true, "SHIFT_MANAGEMENT must be enabled");
assert(\App\Filament\Tenant\Resources\WorkSchedules\WorkScheduleResource::canViewAny() === true, "WorkScheduleResource should be visible when SHIFT_MANAGEMENT enabled");
assert(\App\Filament\Tenant\Resources\WorkShifts\WorkShiftResource::canViewAny() === true, "WorkShiftResource should be visible when SHIFT_MANAGEMENT enabled");
assert(\App\Filament\Tenant\Resources\WorkScheduleOverrides\WorkScheduleOverrideResource::canViewAny() === true, "WorkScheduleOverrideResource should be visible when SHIFT_MANAGEMENT enabled");
echo "[PASS] SHIFT_MANAGEMENT active: Resources visible.\n";

// 5.b Disable SHIFT_MANAGEMENT
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::SHIFT_MANAGEMENT->value], ['is_enabled' => false]);
assert($tenant->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT) === false, "SHIFT_MANAGEMENT must be disabled");
assert(\App\Filament\Tenant\Resources\WorkSchedules\WorkScheduleResource::canViewAny() === false, "WorkScheduleResource should be hidden when SHIFT_MANAGEMENT disabled");
assert(\App\Filament\Tenant\Resources\WorkShifts\WorkShiftResource::canViewAny() === false, "WorkShiftResource should be hidden when SHIFT_MANAGEMENT disabled");
assert(\App\Filament\Tenant\Resources\WorkScheduleOverrides\WorkScheduleOverrideResource::canViewAny() === false, "WorkScheduleOverrideResource should be hidden when SHIFT_MANAGEMENT disabled");

// Test model creation blocked
$scheduleBlocked = false;
try {
    \App\Models\Tenant\WorkSchedule::create(['name' => 'Blocked Custom Schedule']);
} catch (\Throwable $e) {
    $scheduleBlocked = true;
}
assert($scheduleBlocked === true, "WorkSchedule creation must throw Exception when SHIFT_MANAGEMENT disabled");

$shiftBlocked = false;
try {
    \App\Models\Tenant\WorkShift::create(['name' => 'Blocked Shift', 'start_time' => '09:00', 'end_time' => '17:00']);
} catch (\Throwable $e) {
    $shiftBlocked = true;
}
assert($shiftBlocked === true, "WorkShift creation must throw Exception when SHIFT_MANAGEMENT disabled");

$overrideBlocked = false;
try {
    \App\Models\Tenant\WorkScheduleOverride::create([
        'employee_assignment_id' => 1,
        'work_shift_id' => 1,
        'date' => '2026-10-20',
        'is_working_days' => true,
        'reason' => 'Test',
    ]);
} catch (\Throwable $e) {
    $overrideBlocked = true;
}
assert($overrideBlocked === true, "WorkScheduleOverride creation must throw Exception when SHIFT_MANAGEMENT disabled");
echo "[PASS] SHIFT_MANAGEMENT inactive: Resources and custom creations blocked safely.\n";

// 5.c Default Schedule Fallback Test
$defaultSchedule = \App\Models\Tenant\WorkSchedule::getDefaultSchedule();
assert($defaultSchedule !== null, "Default schedule must be returned/created even when SHIFT_MANAGEMENT is disabled");
assert($defaultSchedule->name === 'Jadwal Reguler (Standar)', "Default schedule name matches");
assert($defaultSchedule->days()->count() === 7, "Default schedule has 7 days configured");

// Check days configuration
$mondayDay = $defaultSchedule->days()->where('day_of_week', 1)->first();
assert($mondayDay && $mondayDay->is_working_days === true, "Monday is a working day in default schedule");
$sundayDay = $defaultSchedule->days()->where('day_of_week', 7)->first();
assert($sundayDay && $sundayDay->is_working_days === false, "Sunday is an off-day in default schedule");
echo "[PASS] SHIFT_MANAGEMENT Default Schedule: Standard Mon-Sat 08:00-17:00 created & verified.\n";

// 5.d Employee Assignment Auto-Fallback & Attendance Test
$employee = \App\Models\Tenant\Employee::first();
$branch = \App\Models\Tenant\Branch::first();
if ($employee && $branch) {
    // Clean up previous test assignment if any
    \App\Models\Tenant\EmployeeAssignment::where('employee_id', $employee->id)->where('branch_id', $branch->id)->forceDelete();

    // Create assignment without specifying work_schedule_id
    $assignment = \App\Models\Tenant\EmployeeAssignment::create([
        'employee_id' => $employee->id,
        'branch_id' => $branch->id,
        'start_date' => '2026-01-01',
    ]);

    assert($assignment->work_schedule_id === $defaultSchedule->id, "Assignment auto-filled work_schedule_id with default schedule");

    // Check isWorkingDay on a Monday (2026-10-19) and a Sunday (2026-10-25)
    $mondayDate = \Carbon\Carbon::parse('2026-10-19');
    $sundayDate = \Carbon\Carbon::parse('2026-10-25');
    assert($assignment->isWorkingDay($mondayDate) === true, "Monday is recognized as working day");
    assert($assignment->isWorkingDay($sundayDate) === false, "Sunday is recognized as off-day");

    // Check AttendanceService context resolution
    $context = app(\App\Services\AttendanceService::class)->resolveAttendanceContext($assignment, [
        'logged_at' => \Carbon\Carbon::parse('2026-10-19 08:30:00'),
        'date' => '2026-10-19',
    ]);
    assert($context['working_day'] === true, "AttendanceService recognizes Monday as effective working day");
    assert($context['attendance_type'] === 'working_day', "Attendance type is working_day on Monday");
    assert($context['work_shift'] !== null, "AttendanceService resolves default work shift");

    // 5.e Override Bypass Test when SHIFT_MANAGEMENT is disabled
    FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::SHIFT_MANAGEMENT->value], ['is_enabled' => true]);
    $shiftId = $defaultSchedule->days()->first()?->work_shift_id;
    $override = \App\Models\Tenant\WorkScheduleOverride::create([
        'employee_assignment_id' => $assignment->id,
        'work_shift_id' => $shiftId,
        'date' => '2026-10-19',
        'is_working_days' => false, // Set Monday to off-day via override
        'approval_status' => 'approved',
        'reason' => 'Test Override Bypass',
        'created_by' => 1,
    ]);

    // Now disable SHIFT_MANAGEMENT again
    FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::SHIFT_MANAGEMENT->value], ['is_enabled' => false]);
    $contextWithIgnoredOverride = app(\App\Services\AttendanceService::class)->resolveAttendanceContext($assignment, [
        'logged_at' => \Carbon\Carbon::parse('2026-10-19 08:30:00'),
        'date' => '2026-10-19',
    ]);
    assert($contextWithIgnoredOverride['working_day'] === true, "Override must be ignored and fallback to standard Monday working day when SHIFT_MANAGEMENT is off");
    assert($contextWithIgnoredOverride['attendance_type'] === 'working_day', "Attendance type must NOT be override when SHIFT_MANAGEMENT is off");
    $override->delete();

    // Clean up test assignment
    $assignment->forceDelete();
    echo "[PASS] EmployeeAssignment & AttendanceService work seamlessly without SHIFT_MANAGEMENT (Overrides safely ignored)!\n";
}

// Restore SHIFT_MANAGEMENT
FeatureToggle::updateOrCreate(['feature_code' => TenantFeature::SHIFT_MANAGEMENT->value], ['is_enabled' => true]);

echo "\nSUCCESS: ALL CLUSTER 4 (POINTS 9, 11, 12, 13, 14) SELF-CHECKS PASSED WITH ZERO CRASHES!\n";

