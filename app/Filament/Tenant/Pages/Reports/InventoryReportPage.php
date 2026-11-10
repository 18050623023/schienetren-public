<?php

namespace App\Filament\Tenant\Pages\Reports;

use App\Enums\TenantFeature;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Product;
use App\Services\Reports\InventoryReportService;
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

class InventoryReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan Stok & Gudang';
    protected static ?string $title = 'Laporan Stok & Gudang';

    protected string $view = 'filament.tenant.pages.reports.inventory-report-page';

    public ?array $data = [];
    public string $activeTab = 'valuation'; // 'valuation', 'movements', 'opnames', 'transfers', 'purchases'

    /**
     * Membatasi hak akses hanya untuk Owner, Admin, dan Gudang/Logistik.
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
            || $user->hasRole(['warehouse', 'logistic', 'manager']);
    }

    public function mount(): void
    {
        $currentBranchId = session('current_branch_id');

        $this->form->fill([
            'branch_id' => $currentBranchId,
            'date_from' => Carbon::now()->startOfMonth()->toDateString(),
            'date_to' => Carbon::now()->endOfMonth()->toDateString(),
            'product_id' => null,
            'direction' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Inventaris & Periode')
                    ->description('Saring data berdasarkan cabang, rentang tanggal, produk, atau arah arus barang.')
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
                                ->live(),

                            DatePicker::make('date_to')
                                ->label('Sampai Tanggal')
                                ->default(Carbon::now()->endOfMonth())
                                ->live(),

                            Select::make('product_id')
                                ->label('Produk / Sparepart')
                                ->placeholder('Semua Produk')
                                ->options(function () {
                                    return Product::with('brand')
                                        ->get()
                                        ->mapWithKeys(fn ($p) => [$p->id => ($p->brand?->name ? $p->brand->name . ' - ' : '') . $p->name]);
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

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['valuation', 'movements', 'opnames', 'transfers', 'purchases'])) {
            $this->activeTab = $tab;
        }
    }

    public function getReportDataProperty(): array
    {
        $service = app(InventoryReportService::class);
        $filters = $this->data ?? [];

        return match ($this->activeTab) {
            'movements' => $service->getStockMovementReport($filters),
            'opnames' => $service->getStockOpnameReport($filters),
            'transfers' => $service->getStockTransferReport($filters),
            'purchases' => $service->getPurchasingReport($filters),
            default => $service->getStockValuationReport($filters),
        };
    }

    public function exportCsv()
    {
        $service = app(InventoryReportService::class);
        $filters = $this->data ?? [];

        return match ($this->activeTab) {
            'movements' => $service->exportStockMovementCsv($filters),
            'opnames' => $service->exportStockOpnameCsv($filters),
            'transfers' => $service->exportStockTransferCsv($filters),
            'purchases' => $service->exportPurchasingCsv($filters),
            default => $service->exportStockValuationCsv($filters),
        };
    }

    public function printReport(): void
    {
        $params = array_merge($this->data ?? [], [
            'tab' => $this->activeTab,
        ]);

        $url = route('tenant.reports.inventory.print', array_filter($params));
        $this->dispatch('open-print-tab', url: $url);
    }
}
