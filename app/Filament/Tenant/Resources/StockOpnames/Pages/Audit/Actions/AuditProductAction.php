<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Actions;

use App\Models\Tenant\StockOpname;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Forms\AuditProductForm;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Actions\ActionSubmit;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Support\AuditStockOpnameHelper;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class AuditProductAction
{
    public static function make(
        int $branchId,
        ?StockOpname $stockOpname
    ): Action {
        return Action::make('audit')
            ->label(fn ($record) => AuditStockOpnameHelper::isAudited($record, $stockOpname) ? 'Edit' : 'Audit')
            ->icon('heroicon-o-pencil')
            ->color(fn ($record) => AuditStockOpnameHelper::isAudited($record, $stockOpname) ? 'success' : 'primary')
            ->visible(fn () =>
                $stockOpname?->status === 'in_progress'
            )

            ->modalHeading(fn ($record) =>
                'Audit Product '. $record->brand->name.' ' . $record->name
            )

            ->modalSubmitActionLabel('Simpan')

            ->mountUsing( function ( $form, $record) use ($branchId, $stockOpname) {

                // $branchId = session('current_branch_id');
                if (! $record || ! $branchId) return;

                if (AuditStockOpnameHelper::isAudited($record, $stockOpname)) {
                    // dd(VariantLoader::edit($stockOpname, $record, $branchId));
                    $form->fill([
                        'action' => 'edit',
                        'items' => VariantLoader::edit($stockOpname, $record, $branchId),
                    ]);
                    return;
                }

                $form->fill([
                    'items' => VariantLoader::load($record, $branchId),
                ]);
            })

            ->form(fn ($record) =>
                AuditProductForm::schema($record)
            )

            ->action(function ($record, array $data) use ($branchId, $stockOpname) {
                // dd($data);
                if (! $stockOpname) {
                    Notification::make()
                    ->title('Mulai audit terlebih dahulu')
                    ->success()
                    ->send();
                    return;
                }

                ActionSubmit::submit($data, $stockOpname, $branchId);

                // dd($data);
                Notification::make()
                    ->title('Audit produk berhasil disimpan')
                    ->success()
                    ->send();
            });
    }
}
