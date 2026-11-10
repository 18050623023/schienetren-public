<?php

namespace App\Filament\Tenant\Resources\GoodsReceipts\Tables;

use App\Filament\Tenant\Resources\GoodsReceipts\Actions\GoodsReceiptActionStatus;
use App\Filament\Tenant\Resources\GoodsReceipts\GoodsReceiptsResource;
use App\Models\Tenant\GoodsReceipts;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class GoodsReceiptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchaseOrder.code')
                    ->searchable(),
                TextColumn::make('purchaseOrder.supplier.name')
                    ->searchable(),
                TextColumn::make('purchaseOrder.branch.name')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'posted' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('attachment')
                    ->label('Surat Jalan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Ada' : '-')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    Action::make('post')
                        ->label('Post / Konfirmasi Penerimaan')
                        ->icon('heroicon-s-arrow-up-on-square-stack')
                        ->color('success')
                        ->visible(fn ($record) => in_array($record->status, ['draft']))
                        ->requiresConfirmation()
                        ->modalHeading('Post Receive')
                        ->modalDescription(new HtmlString(
                            'Yakin post Receive?. <br>' .
                            'Receive Goods tidak bisa diubah setelah di konfirmasi karena akan mempengaruhi stock, ' .
                            'Pastikan semua isian sudah benar.'
                        ))
                        ->action(fn (GoodsReceipts $record) =>
                            GoodsReceiptActionStatus::confirmAction($record)
                        ),

                    Action::make('Cancel')
                        ->icon('heroicon-s-arrow-down-on-square-stack')
                        ->color('danger')
                        ->visible(fn ($record) => in_array($record->status, ['draft']))
                        ->requiresConfirmation()
                        ->modalHeading('Cancel Receive')
                        ->modalDescription(new HtmlString(
                            'Yakin Cancel Receive?. <br>' .
                            'Receive Goods tidak bisa diubah setelah di cancel, ' .
                            'Pastikan semua isian sudah benar.'
                        ))
                        ->action(fn (GoodsReceipts $record) =>
                            GoodsReceiptActionStatus::cancelAction($record)
                        ),

                    ViewAction::make(),

                    EditAction::make()
                        ->visible(fn ($record) => in_array($record->status, ['draft']) ),

                    DeleteAction::make()
                        ->visible(fn ($record) => in_array($record->status, ['canceled']) ),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
