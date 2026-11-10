<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages\Audit;

use App\Enums\TenantFeature;
use Filament\Resources\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use App\Filament\Tenant\Resources\StockOpnames\StockOpnameResource;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Concerns\HasAuditActions;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Tables\AuditProductTable;
use App\Models\Tenant\Stock;
use App\Models\Tenant\StockOpname;
use Filament\Notifications\Notification;
use Filament\Panel\Concerns\HasUnsavedChangesAlerts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\Url;

class AuditStockOpname extends Page implements HasTable
{
    use InteractsWithTable;
    use HasAuditActions;
    use HasUnsavedChangesAlerts;

    protected static string $resource = StockOpnameResource::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(array $parameters = []): bool
    {
        return tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME);
    }

    public function getSubNavigation(): array
    {
        return static::getResource()::getSubNavigation();
    }

    protected string $view =
        'filament.tenant.resources.stock-opnames.pages.audit-stock-opname';


    #[Url]
    public ?string $token = null;

    public array $items = [];

    public ?int $branchId = null;
    public ?StockOpname $stockOpname = null;


    public function mount(): void
    {
        abort_unless(tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME), 403, 'Fitur Stock Opname tidak aktif.');

        $this->branchId = filled(session('current_branch_id')) ? (int) session('current_branch_id') : null;

        if (!$this->branchId) {
            Notification::make()
                ->title('Mohon pilih cabang terlebih dahulu')
                ->warning()
                ->send();
            return;
        }

        $stock = Stock::where('branch_id', $this->branchId)->exists();
        // dd($stock);
        if (!$stock) {
            Notification::make()
                ->title('Stock Belum tersedia, Mohon Post Stock terlebih dahulu di Daftar Stock Opname')
                ->warning()
                ->send();

            return;
        }

        // Dari daftar stock opname dengan token
        if ($this->token) {
            try {
                [$id, $action] = explode('|', Crypt::decryptString($this->token));

                $this->stockOpname = StockOpname::find($id);

                session(['current_branch_id' => $this->stockOpname->branch_id]);
                $this->branchId = $this->stockOpname->branch_id;
                if ($action == 'edit') {
                    $this->stockOpname->update([
                        'status' => 'in_progress',
                    ]);
                }
            } catch (\Throwable $e) {
                Notification::make()
                    ->title('Token audit tidak valid')
                    ->danger()
                    ->send();

                redirect(StockOpnameResource::getUrl('index'));
                return;
            }
        }


        // deteksi apakah ada stock opname sedang berjalan
        $stockOpname = StockOpname::query()
                ->where('status', 'in_progress')
                ->where('branch_id', $this->branchId)
                ->exists();

        if ($stockOpname) {
            $this->stockOpname = StockOpname::query()
                ->where('status', 'in_progress')
                ->where('branch_id', $this->branchId)
                ->first();
        }


        // deteksi apakah ada stock opname hari ini
        if (! $this->stockOpname) {

            if (! session()->has('current_branch_id')) {
                Notification::make()
                    ->title('Silakan pilih branch terlebih dahulu')
                    ->warning()
                    ->send();
                return;
            }


            $this->stockOpname = StockOpname::query()
                ->where('branch_id', $this->branchId)
                ->where('type', 'regular')
                ->whereDate('date', today())
                ->latest()
                ->first();
            // dd($this->id);
        }

        // ⛔ FINAL GUARD
        // if (! $this->stockOpname) {
        //     Notification::make()
        //         ->title('Stock opname hari ini belum tersedia')
        //         ->danger()
        //         ->send();

        //     $this->redirect(StockOpnameResource::getUrl('index'));
        //     return;
        // }

        // session(['current_branch_id' => $this->stockOpname->branch_id]);
        // $this->branchId = $this->stockOpname->branch_id;
    }

    // public function mount(Request $request): void
    // {

    //     $stockOpnameId = $request->get('stock_opname_id');

    //     // 🔁 JIKA DARI TABLE ACTION
    //     if ($stockOpnameId) {
    //         $this->stockOpname = StockOpname::find($stockOpnameId);
    //     }

    //     if (! session()->has('current_branch_id') || session('current_branch_id') == 0) {
    //         Notification::make()
    //             ->title('Silakan pilih branch terlebih dahulu')
    //             ->warning()
    //             ->send();

    //         return;
    //     }else {

    //         $this->branchId = session('current_branch_id');
    //     }


    //     $this->stockOpname = StockOpname::query()
    //         ->where('branch_id', $this->branchId)
    //         ->where('type', 'regular')
    //         ->whereDate('date', today())
    //         ->latest()
    //         ->first();
    // }

    public function table(Table $table): Table
    {
        return AuditProductTable::make(
            table: $table,
            branchId: $this->branchId??0,
            stockOpname: $this->stockOpname
        );
    }


    protected function getUnsavedChangesAlertHeading(): string
    {
        return 'Perubahan belum disimpan!';
    }

    protected function getUnsavedChangesAlertDescription(): string
    {
        return 'Jika Anda meninggalkan halaman audit ini, seluruh data yang belum disimpan akan hilang.';
    }

    protected function getUnsavedChangesAlertCancelButtonLabel(): string
    {
        return 'Tetap di halaman';
    }

    protected function getUnsavedChangesAlertConfirmButtonLabel(): string
    {
        return 'Tinggalkan halaman';
    }

}
