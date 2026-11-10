<?php

namespace App\Filament\Tenant\Pages\Reports;

use App\Enums\RepairStatus;
use App\Enums\TenantFeature;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Brand;
use App\Models\Tenant\DeviceType;
use App\Models\Tenant\EmployeeAssignment;
use App\Services\Reports\ServiceReportService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ServiceReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan Servis & Analitik';
    protected static ?string $title = 'Laporan Terpadu Operasional & Analitik Servis';

    protected string $view = 'filament.tenant.pages.reports.service-report-page';

    public ?array $data = [];
    public string $activeTab = 'operations'; // 'operations', 'technicians', 'fault_trends', 'crm', 'warranty'

    /**
     * Hak akses laporan servis untuk Owner, Admin, CS, dan Teknisi/Manager.
     */
    public static function canAccess(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::ADVANCED_REPORTS)) {
            return false;
        }

        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $allowedRoles = array_merge(
            (array) settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']),
            (array) settingTenant('tenant_cs_roles', ['customer_service']),
            (array) settingTenant('tenant_technical_roles', ['teknisi', 'technician']),
            ['manager', 'head_technician']
        );

        return $user->hasRole($allowedRoles);
    }

    public function mount(): void
    {
        $this->form->fill([
            'branch_id' => session('current_branch_id'),
            'date_from' => Carbon::now()->startOfMonth()->toDateString(),
            'date_to' => Carbon::now()->endOfMonth()->toDateString(),
            'status' => null,
            'technician_id' => null,
            'brand_id' => null,
            'device_type_id' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Laporan Servis & Periode')
                    ->description('Saring data menurut cabang, rentang tanggal masuk/klaim, teknisi penanggung jawab, tipe unit, atau merek.')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                            'xl' => 6,
                        ])->schema([
                            Select::make('branch_id')
                                ->label('Cabang')
                                ->placeholder('Semua Cabang')
                                ->options(Branch::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->live(),

                            DatePicker::make('date_from')
                                ->label('Dari Tanggal')
                                ->default(Carbon::now()->startOfMonth())
                                ->live(),

                            DatePicker::make('date_to')
                                ->label('Sampai Tanggal')
                                ->default(Carbon::now()->endOfMonth())
                                ->live(),

                            Select::make('technician_id')
                                ->label('Teknisi')
                                ->placeholder('Semua Teknisi')
                                ->options(function () {
                                    return EmployeeAssignment::with('employee.user')
                                        ->get()
                                        ->mapWithKeys(function ($assignment) {
                                            $name = $assignment->employee?->name ?? 'Pegawai #' . $assignment->id;
                                            return [$assignment->id => $name];
                                        });
                                })
                                ->searchable()
                                ->preload()
                                ->live(),

                            Select::make('brand_id')
                                ->label('Brand')
                                ->placeholder('Semua Merek')
                                ->options(Brand::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->live(),

                            Select::make('device_type_id')
                                ->label('Tipe Perangkat')
                                ->placeholder('Semua Tipe')
                                ->options(DeviceType::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->live(),
                        ]),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['operations', 'technicians', 'fault_trends', 'crm', 'warranty'])) {
            $this->activeTab = $tab;
        }
    }

    public function getReportDataProperty(): array
    {
        $service = app(ServiceReportService::class);
        $filters = $this->data ?? [];

        return match ($this->activeTab) {
            'technicians' => $service->getTechnicianPerformanceReport($filters),
            'fault_trends' => $service->getDeviceFaultAnalyticsReport($filters),
            'crm' => $service->getCustomerInsightsReport($filters),
            'warranty' => $service->getWarrantyClaimReport($filters),
            default => $service->getServiceOperationsReport($filters),
        };
    }

    public function exportCsv()
    {
        $service = app(ServiceReportService::class);
        $filters = $this->data ?? [];

        return $service->exportCsv($this->activeTab, $filters);
    }

    public function printReport(): void
    {
        $params = array_merge($this->data ?? [], [
            'tab' => $this->activeTab,
        ]);

        $url = route('tenant.reports.service.print', array_filter($params));
        $this->dispatch('open-print-tab', url: $url);
    }
}
