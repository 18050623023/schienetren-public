<?php

declare(strict_types=1);

use App\Enums\TenantFeature;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    // 'web',
])
// ->domain(env('TENANCY_DOMAIN'))
->group(function () {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });

    Route::get('/cek-status', App\Livewire\ClientRepairStatus::class)->name('client.repair.status')->middleware(['throttle:15,1']);
    
    Route::get('/repairs/{repair}/print', function (\App\Models\Tenant\Repair $repair) {
        abort_unless(auth('tenant')->user()?->can('view', $repair), 403);
        $repair->load(['client', 'branch', 'repairDevices.deviceType', 'repairDevices.repairItems', 'receivedBy']);
        return view('print.nota-pengambilan', compact('repair'));
    })->name('tenant.repairs.print')->middleware(['auth:tenant']);

    Route::get('/pos/sales/{sale}/print', function (\App\Models\Tenant\Sale $sale) {
        abort_unless(auth('tenant')->user()?->can('view', $sale), 403);
        $sale->load(['client', 'branch', 'cashier', 'paymentMethod', 'items.productVariant.product', 'items.serials']);
        return view('print.pos-receipt', compact('sale'));
    })->name('tenant.pos.sales.print')->middleware(['auth:tenant']);

    Route::get('/reports/hr/print', function (\Illuminate\Http\Request $request, \App\Services\Reports\HrReportService $service) {
        abort_unless(tenant()->hasFeatureCode(TenantFeature::ADVANCED_REPORTS), 403);
        $user = auth('tenant')->user();
        abort_unless($user && ($user->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])) || $user->hasRole(['hr', 'manager'])), 403);

        $filters = [
            'branch_id' => $request->get('branch_id') ?: session('current_branch_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'period' => $request->get('period'),
            'employee_id' => $request->get('employee_id'),
        ];
        $tab = $request->get('tab', 'attendance');

        $reportData = match ($tab) {
            'leave' => $service->getLeaveReport($filters),
            'payroll' => $service->getPayrollReport($filters),
            default => $service->getAttendanceReport($filters),
        };

        $branch = $filters['branch_id'] ? \App\Models\Tenant\Branch::find($filters['branch_id']) : null;
        $employee = $filters['employee_id'] ? \App\Models\Tenant\Employee::with('user')->find($filters['employee_id']) : null;

        return view('print.reports.hr-report', compact('reportData', 'filters', 'tab', 'branch', 'employee'));
    })->name('tenant.reports.hr.print')->middleware(['auth:tenant']);

    Route::get('/reports/inventory/print', function (\Illuminate\Http\Request $request, \App\Services\Reports\InventoryReportService $service) {
        abort_unless(tenant()->hasFeatureCode(TenantFeature::ADVANCED_REPORTS), 403);
        $user = auth('tenant')->user();
        abort_unless($user && ($user->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])) || $user->hasRole(['warehouse', 'logistic', 'manager'])), 403);

        $filters = [
            'branch_id' => $request->get('branch_id') ?: session('current_branch_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'product_id' => $request->get('product_id'),
            'direction' => $request->get('direction'),
            'supplier_id' => $request->get('supplier_id'),
            'status' => $request->get('status'),
        ];
        $tab = $request->get('tab', 'valuation');

        $reportData = match ($tab) {
            'movements' => $service->getStockMovementReport($filters),
            'opnames' => $service->getStockOpnameReport($filters),
            'transfers' => $service->getStockTransferReport($filters),
            'purchases' => $service->getPurchasingReport($filters),
            default => $service->getStockValuationReport($filters),
        };

        $branch = $filters['branch_id'] ? \App\Models\Tenant\Branch::find($filters['branch_id']) : null;
        $product = $filters['product_id'] ? \App\Models\Tenant\Product::find($filters['product_id']) : null;

        return view('print.reports.inventory-report', compact('reportData', 'filters', 'tab', 'branch', 'product'));
    })->name('tenant.reports.inventory.print')->middleware(['auth:tenant']);

    Route::get('/reports/service/print', function (\Illuminate\Http\Request $request, \App\Services\Reports\ServiceReportService $service) {
        abort_unless(tenant()->hasFeatureCode(TenantFeature::ADVANCED_REPORTS), 403);
        $user = auth('tenant')->user();
        $allowedRoles = array_merge(
            (array) settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']),
            (array) settingTenant('tenant_cs_roles', ['customer_service']),
            (array) settingTenant('tenant_technical_roles', ['teknisi', 'technician']),
            ['manager', 'head_technician']
        );
        abort_unless($user && $user->hasRole($allowedRoles), 403);

        $filters = [
            'branch_id' => $request->get('branch_id') ?: session('current_branch_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'status' => $request->get('status'),
            'technician_id' => $request->get('technician_id'),
            'brand_id' => $request->get('brand_id'),
            'device_type_id' => $request->get('device_type_id'),
        ];
        $tab = $request->get('tab', 'operations');

        $reportData = match ($tab) {
            'technicians' => $service->getTechnicianPerformanceReport($filters),
            'fault_trends' => $service->getDeviceFaultAnalyticsReport($filters),
            'crm' => $service->getCustomerInsightsReport($filters),
            'warranty' => $service->getWarrantyClaimReport($filters),
            default => $service->getServiceOperationsReport($filters),
        };

        $branch = $filters['branch_id'] ? \App\Models\Tenant\Branch::find($filters['branch_id']) : null;
        $technician = $filters['technician_id'] ? \App\Models\Tenant\EmployeeAssignment::with('employee.user')->find($filters['technician_id']) : null;
        $brand = $filters['brand_id'] ? \App\Models\Tenant\Brand::find($filters['brand_id']) : null;
        $deviceType = $filters['device_type_id'] ? \App\Models\Tenant\DeviceType::find($filters['device_type_id']) : null;

        return view('print.reports.service-report', compact('reportData', 'filters', 'tab', 'branch', 'technician', 'brand', 'deviceType'));
    })->name('tenant.reports.service.print')->middleware(['auth:tenant']);

    Route::get('/reports/finance/print', function (\Illuminate\Http\Request $request, \App\Services\Reports\FinanceReportService $service) {
        abort_unless(tenant()->hasFeatureCode(TenantFeature::ADVANCED_REPORTS), 403);
        $user = auth('tenant')->user();
        $allowedRoles = array_merge(
            (array) settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']),
            ['manager', 'finance', 'head_finance']
        );
        abort_unless($user && $user->hasRole($allowedRoles), 403);

        $filters = [
            'branch_id' => $request->get('branch_id') ?: session('current_branch_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'payment_method_id' => $request->get('payment_method_id'),
            'supplier_id' => $request->get('supplier_id'),
        ];
        $tab = $request->get('tab', 'cashflow');

        $reportData = match ($tab) {
            'profit_loss' => $service->getProfitLossReport($filters),
            'receivables' => $service->getCustomerReceivablesReport($filters),
            'payables' => $service->getSupplierPayablesReport($filters),
            'expenses' => $service->getExpensesReport($filters),
            default => $service->getCashFlowReport($filters),
        };

        $branch = $filters['branch_id'] ? \App\Models\Tenant\Branch::find($filters['branch_id']) : null;
        $paymentMethod = $filters['payment_method_id'] ? \App\Models\Tenant\PaymentMethod::find($filters['payment_method_id']) : null;

        return view('print.reports.finance-report', compact('reportData', 'filters', 'tab', 'branch', 'paymentMethod'));
    })->name('tenant.reports.finance.print')->middleware(['auth:tenant']);

    // Route::post('/switch-branch', function () {
    //     session(['current_branch_id' => request('branch_id')]);
    //     return back();
    // })->name('switch-branch')->middleware(['auth:tenant']);

});
