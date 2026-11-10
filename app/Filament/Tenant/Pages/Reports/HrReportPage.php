<?php

namespace App\Filament\Tenant\Pages\Reports;

use App\Enums\TenantFeature;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Employee;
use App\Services\Reports\HrReportService;
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

class HrReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan HR';
    protected static ?string $title = 'Laporan Terpusat HR';

    protected string $view = 'filament.tenant.pages.reports.hr-report-page';

    public ?array $data = [];
    public string $activeTab = 'attendance'; // 'attendance', 'leave', 'payroll'

    /**
     * Membatasi hak akses hanya untuk Owner, Admin, dan HR.
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

        return $user->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))
            || $user->hasRole(['hr', 'manager']);
    }

    public function mount(): void
    {
        $currentBranchId = session('current_branch_id');

        $this->form->fill([
            'branch_id' => $currentBranchId,
            'date_from' => Carbon::now()->startOfMonth()->toDateString(),
            'date_to' => Carbon::now()->endOfMonth()->toDateString(),
            'period' => Carbon::now()->format('Y-m'),
            'employee_id' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Laporan HR')
                    ->description('Sesuaikan cabang, rentang tanggal, atau karyawan untuk menyaring data.')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 4,
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
                                ->live()
                                ->afterStateUpdated(function ($state) {
                                    if ($state) {
                                        $this->data['period'] = Carbon::parse($state)->format('Y-m');
                                    }
                                }),

                            DatePicker::make('date_to')
                                ->label('Sampai Tanggal')
                                ->default(Carbon::now()->endOfMonth())
                                ->live(),

                            Select::make('employee_id')
                                ->label('Karyawan')
                                ->placeholder('Semua Karyawan')
                                ->options(function () {
                                    return Employee::with('user')
                                        ->get()
                                        ->mapWithKeys(fn ($emp) => [$emp->id => ($emp->user?->name ?? 'Karyawan #' . $emp->id) . ' (' . ($emp->code_pegawai ?? '-') . ')']);
                                })
                                ->searchable()
                                ->preload()
                                ->live(),
                        ]),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    /**
     * Berpindah tab tampilan (attendance / leave / payroll).
     */
    public function setTab(string $tab): void
    {
        if (in_array($tab, ['attendance', 'leave', 'payroll'])) {
            $this->activeTab = $tab;
        }
    }

    /**
     * Mengambil data laporan berdasarkan tab yang aktif.
     */
    public function getReportDataProperty(): array
    {
        $service = app(HrReportService::class);
        $filters = $this->data ?? [];

        return match ($this->activeTab) {
            'leave' => $service->getLeaveReport($filters),
            'payroll' => $service->getPayrollReport($filters),
            default => $service->getAttendanceReport($filters),
        };
    }

    /**
     * Download rekap laporan dalam format CSV (native streaming).
     */
    public function exportCsv()
    {
        $service = app(HrReportService::class);
        $filters = $this->data ?? [];

        return match ($this->activeTab) {
            'leave' => $service->exportLeaveCsv($filters),
            'payroll' => $service->exportPayrollCsv($filters),
            default => $service->exportAttendanceCsv($filters),
        };
    }

    /**
     * Buka tampilan cetak nota/laporan resmi.
     */
    public function printReport(): void
    {
        $params = array_merge($this->data ?? [], [
            'tab' => $this->activeTab,
        ]);

        $url = route('tenant.reports.hr.print', array_filter($params));
        $this->dispatch('open-print-tab', url: $url);
    }
}
