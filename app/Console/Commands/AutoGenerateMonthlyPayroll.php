<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Tenant\Branch;
use App\Services\PayrollCalculationService;
use Illuminate\Console\Command;

class AutoGenerateMonthlyPayroll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:auto-generate {--period= : Periode penggajian format YYYY-MM (default: bulan ini)} {--tenant= : ID Tenant tertentu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatisasi kalkulasi draf slip gaji bulanan untuk semua cabang & karyawan';

    /**
     * Execute the console command.
     */
    public function handle(PayrollCalculationService $payrollService): void
    {
        $period = $this->option('period') ?: now()->format('Y-m');
        $targetTenant = $this->option('tenant');

        $this->info("Menjalankan kalkulasi payroll periode: [{$period}]...");

        if (function_exists('tenancy') && tenancy()->initialized) {
            $this->processTenantBranches($payrollService, $period);
            $this->info("Selesai memproses payroll pada tenant aktif.");
            return;
        }

        $query = Tenant::query();
        if ($targetTenant) {
            $query->where('id', $targetTenant);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn("Tidak ada tenant yang ditemukan.");
            return;
        }

        foreach ($tenants as $tenant) {
            $tenant->run(function () use ($payrollService, $period, $tenant) {
                $this->line("Memproses Tenant: {$tenant->id}...");
                $this->processTenantBranches($payrollService, $period, $tenant->id);
            });
        }

        $this->info("Seluruh proses auto-generate payroll selesai dengan sukses!");
    }

    /**
     * Proses kalkulasi per cabang pada tenant yang sedang aktif.
     */
    protected function processTenantBranches(PayrollCalculationService $payrollService, string $period, ?string $tenantId = null): void
    {
        $branches = Branch::all();
        $totalGenerated = 0;

        foreach ($branches as $branch) {
            $payrolls = $payrollService->generatePeriodPayroll($branch->id, $period);
            $totalGenerated += $payrolls->count();
        }

        $tenantLabel = $tenantId ? "[Tenant {$tenantId}] " : "";
        $this->info("  {$tenantLabel}Berhasil generate {$totalGenerated} draf slip gaji di {$branches->count()} cabang.");
    }
}
