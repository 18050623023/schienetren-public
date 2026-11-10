<?php

namespace App\Services;

use App\Enums\CompensationPackageComponentBasedOn;
use App\Enums\TenantFeature;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\Branch;
use App\Models\Tenant\CompensationPackage;
use App\Models\Tenant\Employee;
use App\Models\Tenant\EmployeeAssignment;
use App\Models\Tenant\HrPayroll;
use App\Models\Tenant\RepairDevice;
use App\Models\Tenant\RepairTechnicianCommission;
use App\Models\Tenant\Sale;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollCalculationService
{
    /**
     * Generate payroll untuk 1 karyawan atau semua karyawan di cabang dan periode tertentu.
     */
    public function generatePeriodPayroll(int $branchId, string $period, ?int $employeeId = null): Collection
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PAYROLL)) {
            return collect();
        }

        $startDate = Carbon::parse($period . '-01')->startOfMonth();
        $endDate = Carbon::parse($period . '-01')->endOfMonth();

        $query = EmployeeAssignment::query()
            ->where('branch_id', $branchId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->toDateString())
                  ->where(function ($sq) use ($startDate) {
                      $sq->whereNull('end_date')
                         ->orWhere('end_date', '>=', $startDate->toDateString());
                  });
            });

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $assignments = $query->with(['employee'])->get()->unique('employee_id');
        $generatedPayrolls = collect();

        foreach ($assignments as $assignment) {
            if ($assignment->employee) {
                $payroll = $this->calculatePayroll($assignment->employee->id, $period, $branchId, $assignment);
                if ($payroll) {
                    $generatedPayrolls->push($payroll);
                }
            }
        }

        return $generatedPayrolls;
    }

    /**
     * Hitung payroll untuk seorang karyawan berdasarkan paket kompensasi aktifnya.
     */
    public function calculatePayroll(int $employeeId, string $period, int $branchId, ?EmployeeAssignment $assignment = null): ?HrPayroll
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PAYROLL)) {
            return null;
        }

        $startDate = Carbon::parse($period . '-01')->startOfMonth();
        $endDate = Carbon::parse($period . '-01')->endOfMonth();

        if (!$assignment) {
            $assignment = EmployeeAssignment::where('employee_id', $employeeId)
                ->where('branch_id', $branchId)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate->toDateString())
                      ->where(function ($sq) use ($startDate) {
                          $sq->whereNull('end_date')
                             ->orWhere('end_date', '>=', $startDate->toDateString());
                      });
                })
                ->latest('start_date')
                ->first();
        }

        if (!$assignment) {
            return null;
        }

        // Cari paket kompensasi aktif untuk penugasan ini
        $compensationPackage = $this->resolveCompensationPackage($assignment, $startDate, $endDate);

        $basicSalary = 0;
        $totalAllowance = 0;
        $totalCommission = 0;
        $totalDeduction = 0;
        $items = [];

        if ($compensationPackage) {
            $compensationPackage->loadMissing(['packageComponents.component']);

            foreach ($compensationPackage->packageComponents as $cpc) {
                $componentModel = $cpc->component;
                $compName = $componentModel?->name ?? 'Komponen';
                $isDeduction = ($componentModel?->type ?? 'earning') === 'deduction';
                $basedOn = $cpc->based_on instanceof \BackedEnum ? $cpc->based_on : CompensationPackageComponentBasedOn::tryFrom($cpc->based_on);

                switch ($basedOn) {
                    case CompensationPackageComponentBasedOn::BasicSalary:
                        $val = (float) $cpc->value;
                        $basicSalary += $val;
                        $items[] = [
                            'type' => 'earning',
                            'name' => $compName,
                            'amount' => $val,
                        ];
                        break;

                    case CompensationPackageComponentBasedOn::Fixed:
                        $val = (float) $cpc->value;
                        if ($isDeduction) {
                            $totalDeduction += $val;
                            $items[] = [
                                'type' => 'deduction',
                                'name' => $compName,
                                'amount' => $val,
                            ];
                        } else {
                            $totalAllowance += $val;
                            $items[] = [
                                'type' => 'earning',
                                'name' => $compName,
                                'amount' => $val,
                            ];
                        }
                        break;

                    case CompensationPackageComponentBasedOn::Attendance:
                        $presentDays = Attendance::where('employee_assignment_id', $assignment->id)
                            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                            ->whereIn('status', ['present', 'late', 'early'])
                            ->count();
                        
                        $val = $presentDays * (float) $cpc->value;
                        $totalAllowance += $val;
                        $items[] = [
                            'type' => 'earning',
                            'name' => "{$compName} ({$presentDays} Hari)",
                            'amount' => $val,
                        ];
                        break;

                    case CompensationPackageComponentBasedOn::Overtime:
                        $totalMinutes = Attendance::where('employee_assignment_id', $assignment->id)
                            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                            ->sum('overtime');

                        $hours = round($totalMinutes / 60, 2);
                        $val = round($hours * (float) $cpc->value);
                        $totalAllowance += $val;
                        $items[] = [
                            'type' => 'earning',
                            'name' => "{$compName} ({$hours} Jam)",
                            'amount' => $val,
                        ];
                        break;

                    case CompensationPackageComponentBasedOn::RepairService:
                        $commissions = (float) RepairTechnicianCommission::whereHas('repairTechnician', function ($q) use ($assignment) {
                                $q->where('employee_assignment_id', $assignment->id);
                            })
                            ->where('compensation_component_id', $cpc->compensation_component_id)
                            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                            ->sum('amount');

                        // Fallback jika belum pernah ter-generate lewat delivered event
                        if ($commissions <= 0) {
                            $commissions = $this->calculateFallbackRepairCommission($assignment, $cpc, $startDate, $endDate, 'service');
                        }

                        $totalCommission += $commissions;
                        $items[] = [
                            'type' => 'commission',
                            'name' => $compName,
                            'amount' => $commissions,
                        ];
                        break;

                    case CompensationPackageComponentBasedOn::RepairItem:
                        $itemCommissions = (float) RepairTechnicianCommission::whereHas('repairTechnician', function ($q) use ($assignment) {
                                $q->where('employee_assignment_id', $assignment->id);
                            })
                            ->where('compensation_component_id', $cpc->compensation_component_id)
                            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                            ->sum('amount');

                        // Fallback jika belum pernah ter-generate lewat delivered event
                        if ($itemCommissions <= 0) {
                            $itemCommissions = $this->calculateFallbackRepairCommission($assignment, $cpc, $startDate, $endDate, 'product');
                        }

                        $totalCommission += $itemCommissions;
                        $items[] = [
                            'type' => 'commission',
                            'name' => $compName,
                            'amount' => $itemCommissions,
                        ];
                        break;

                    case CompensationPackageComponentBasedOn::Sales:
                        $salesTotal = 0;
                        $userId = $assignment->employee?->user_id;
                        if ($userId) {
                            $salesTotal = (float) Sale::completed()
                                ->where('cashier_id', $userId)
                                ->where('branch_id', $assignment->branch_id)
                                ->betweenDates($startDate, $endDate)
                                ->sum('grand_total');
                        }

                        $rate = (float) $cpc->value;
                        $salesCommission = ($rate <= 100 && $rate > 0) ? ($salesTotal * ($rate / 100)) : min($salesTotal, $rate);

                        $totalCommission += $salesCommission;
                        $items[] = [
                            'type' => 'commission',
                            'name' => $compName,
                            'amount' => $salesCommission,
                        ];
                        break;

                    case CompensationPackageComponentBasedOn::Custom:
                    default:
                        $val = (float) $cpc->value;
                        if ($isDeduction) {
                            $totalDeduction += $val;
                            $items[] = [
                                'type' => 'deduction',
                                'name' => $compName,
                                'amount' => $val,
                            ];
                        } else {
                            $totalAllowance += $val;
                            $items[] = [
                                'type' => 'earning',
                                'name' => $compName,
                                'amount' => $val,
                            ];
                        }
                        break;
                }
            }
        }

        $netSalary = max(0, $basicSalary + $totalAllowance + $totalCommission - $totalDeduction);

        return DB::transaction(function () use (
            $branchId,
            $employeeId,
            $period,
            $compensationPackage,
            $basicSalary,
            $totalAllowance,
            $totalCommission,
            $totalDeduction,
            $netSalary,
            $items
        ) {
            $payroll = HrPayroll::updateOrCreate(
                [
                    'branch_id' => $branchId,
                    'employee_id' => $employeeId,
                    'period' => $period,
                ],
                [
                    'compensation_package_id' => $compensationPackage?->id,
                    'basic_salary' => $basicSalary,
                    'total_allowance' => $totalAllowance + $totalCommission,
                    'total_deduction' => $totalDeduction,
                    'net_salary' => $netSalary,
                    'status' => 'draft',
                ]
            );

            // Hapus item lama dan simpan rincian kalkulasi baru secara atomik
            $payroll->items()->delete();
            foreach ($items as $itemData) {
                $payroll->items()->create($itemData);
            }

            return $payroll;
        });
    }

    /**
     * Resolusi paket kompensasi aktif karyawan untuk rentang tanggal tertentu.
     */
    protected function resolveCompensationPackage(EmployeeAssignment $assignment, Carbon $startDate, Carbon $endDate): ?CompensationPackage
    {
        $compAssignment = $assignment->compensationAssignments()
            ->where('start_date', '<=', $endDate->toDateString())
            ->where(function ($q) use ($startDate) {
                $q->whereNull('end_date')
                   ->orWhere('end_date', '>=', $startDate->toDateString());
            })
            ->latest('start_date')
            ->first();

        if ($compAssignment && $compAssignment->package) {
            return $compAssignment->package;
        }

        return $assignment->compensationPackages()->first();
    }

    /**
     * Hitung fallback komisi servis jika data belum tersimpan di repair_technician_commissions.
     */
    protected function calculateFallbackRepairCommission(EmployeeAssignment $assignment, $component, Carbon $startDate, Carbon $endDate, string $targetType): float
    {
        $devices = RepairDevice::whereHas('technicians', fn($q) => $q->where('employee_assignment_id', $assignment->id))
            ->whereHas('repair', fn($q) => $q->where('status', \App\Enums\RepairStatus::Delivered)->whereBetween('delivered_at', [$startDate, $endDate]))
            ->with(['repairItems.productVariant.product'])
            ->get();

        $total = 0;
        foreach ($devices as $device) {
            foreach ($device->repairItems as $item) {
                $productType = $item->productVariant?->product?->type ?? 'product';

                if ($targetType === 'service' && $productType !== 'service') continue;
                if ($targetType === 'product' && $productType === 'service') continue;

                if ($component->calc_type === 'percentage') {
                    $total += ($component->value / 100) * (float) $item->subtotal_item;
                } elseif ($component->calc_type === 'amount_per_unit') {
                    $total += (float) $component->value * ($item->qty ?? 1);
                } elseif ($component->calc_type === 'fixed') {
                    $total += (float) $component->value;
                }
            }
        }

        return $total;
    }
}
