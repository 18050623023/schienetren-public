<?php

namespace App\Filament\Tenant\Pages\Reports;

use App\Enums\TenantFeature;
use App\Models\Tenant\Branch;
use App\Models\Tenant\PaymentMethod;
use App\Services\Reports\FinanceReportService;
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

class FinanceReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan Keuangan';
    protected static ?string $title = 'Laporan Keuangan & Kas Toko';

    protected string $view = 'filament.tenant.pages.reports.finance-report-page';

    public ?array $data = [];
    public string $activeTab = 'cashflow'; // 'cashflow', 'profit_loss', 'receivables', 'payables', 'expenses'

    /**
     * Membatasi hak akses laporan keuangan hanya untuk Owner, Super Admin, Admin, dan Manager.
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
            ['manager', 'finance', 'head_finance']
        );

        return $user->hasRole($allowedRoles);
    }

    public function mount(): void
    {
        $this->form->fill([
            'branch_id' => session('current_branch_id'),
            'date_from' => Carbon::now()->startOfMonth()->toDateString(),
            'date_to' => Carbon::now()->endOfMonth()->toDateString(),
            'payment_method_id' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Periode & Keuangan')
                    ->description('Saring transaksi keuangan menurut cabang, rentang tanggal, atau metode pembayaran kas/bank.')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 4,
                        ])->schema([
                            Select::make('branch_id')
                                ->label('Cabang Toko')
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

                            Select::make('payment_method_id')
                                ->label('Metode Pembayaran')
                                ->placeholder('Semua Metode Kas/Bank')
                                ->options(PaymentMethod::pluck('name', 'id'))
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
        if (in_array($tab, ['cashflow', 'profit_loss', 'receivables', 'payables', 'expenses'])) {
            $this->activeTab = $tab;
        }
    }

    public function getReportDataProperty(): array
    {
        $service = app(FinanceReportService::class);
        $filters = $this->data ?? [];

        return match ($this->activeTab) {
            'profit_loss' => $service->getProfitLossReport($filters),
            'receivables' => $service->getCustomerReceivablesReport($filters),
            'payables' => $service->getSupplierPayablesReport($filters),
            'expenses' => $service->getExpensesReport($filters),
            default => $service->getCashFlowReport($filters),
        };
    }

    public function exportCsv()
    {
        $service = app(FinanceReportService::class);
        $filters = $this->data ?? [];

        return $service->exportCsv($this->activeTab, $filters);
    }

    public function printReport(): void
    {
        $params = array_merge($this->data ?? [], [
            'tab' => $this->activeTab,
        ]);

        $url = route('tenant.reports.finance.print', array_filter($params));
        $this->dispatch('open-print-tab', url: $url);
    }
}
