<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Concerns;

use App\Enums\TenantFeature;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\Tenant\StockOpname;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Support\AuditStockOpnameHelper;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Support\ModalDescriptions;
use App\Filament\Tenant\Resources\StockOpnames\StockOpnameResource;
use App\Models\Tenant\Stock;
use Filament\Facades\Filament;
use Illuminate\Support\HtmlString;

trait HasAuditActions
{
    protected function getHeaderActions(): array
    {
        $actions = [];

        $stock = Stock::where('branch_id', $this->branchId)->exists();
        $stockOpnameDb = StockOpname::where('branch_id', $this->branchId)->where('status', 'draft')->exists();

        if (! $this->stockOpname && $this->branchId && $stock && ! $stockOpnameDb) {
            $actions[] =
                Action::make('mulai_audit')
                    ->label('Mulai Audit')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(fn () => $this->startAudit())
                    ->modalHeading('Confirm Stock Opname')
                    ->modalDescription(ModalDescriptions::mulaiAudit())
                    ->modalCancelActionLabel('Cancel')
                    ->modalSubmitActionLabel('Confirm');
        }

        if ($this->stockOpname?->status === 'in_progress') {
            $actions[] =
                Action::make('selesai_audit')
                    ->label('Selesai Audit')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn () => $this->finishAudit())
                    ->modalHeading('Confirm Stock Opname')
                    ->modalDescription(ModalDescriptions::selesaiAudit());
        }

        return $actions;
    }

    protected function startAudit(): void
    {
        abort_unless(tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME), 403, 'Fitur Stock Opname tidak aktif.');

        $this->stockOpname = StockOpname::create([
            'branch_id'  => $this->branchId,
            'reference'  => AuditStockOpnameHelper::generateReference(),
            'date'       => today(),
            'type'       => 'regular',
            'status'     => 'in_progress',
            'created_by' => Filament::auth()->id(),
        ]);

        Notification::make()
            ->title('Memulai stock opname, Mohon transaksi di hentikan sementara')
            ->info()
            ->send();

        redirect(request()->header('Referer'));
    }

    protected function finishAudit(): void
    {
        abort_unless(tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME), 403, 'Fitur Stock Opname tidak aktif.');

        $this->stockOpname->update([
            'status'       => 'draft',
            'created_at' => now(),
        ]);

        Notification::make()
            ->title('Audit selesai dan dikunci')
            ->success()
            ->send();

        redirect(StockOpnameResource::getUrl('index', ['tab' => 'audit']));
    }
}
