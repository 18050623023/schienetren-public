<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Tables;

use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StockTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $currentBranchId = session('current_branch_id');
                if ($currentBranchId) {
                    $query->where(function ($q) use ($currentBranchId) {
                        $q->where('from_branch_id', $currentBranchId)
                          ->orWhere('to_branch_id', $currentBranchId);
                    });
                }
            })
            ->columns([
                TextColumn::make('code')
                    ->label('Nomor Mutasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('fromBranch.name')
                    ->label('Asal')
                    ->searchable(),
                TextColumn::make('toBranch.name')
                    ->label('Tujuan')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label('Total Item')
                    ->counts('items'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'in_transit' => 'info',
                        'received' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'in_transit' => 'In Transit',
                        'received' => 'Received',
                        'canceled' => 'Canceled',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn ($record) => $record->status === 'draft'),
                    
                    // Custom Lifecycle Actions
                    Action::make('ship')
                        ->label('Kirim Barang')
                        ->icon('heroicon-m-truck')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->status === 'draft' && ($record->from_branch_id == session('current_branch_id') || Auth::user()?->hasRole(['admin', 'owner', 'super_admin'])))
                        ->action(function ($record) {
                            \App\Filament\Tenant\Resources\StockTransfers\Actions\StockTransferActionStatus::shipAction($record);
                        }),
                        
                    Action::make('receive')
                        ->label('Terima Barang')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => in_array($record->status, ['in_transit', 'partial_received']) && ($record->to_branch_id == session('current_branch_id') || Auth::user()?->hasRole(['admin', 'owner', 'super_admin'])))
                        ->fillForm(function ($record) {
                            $items = [];
                            foreach ($record->items as $item) {
                                $sisa = $item->qty_sent - $item->qty_received;
                                if ($sisa <= 0) continue;

                                $serials = [];
                                if ($item->itemSerials->isNotEmpty()) {
                                    $serials = $item->itemSerials->filter(function ($serialRecord) {
                                        return $serialRecord->productSerial && $serialRecord->productSerial->status === 'transfered';
                                    })->pluck('product_serial_id')->map(fn($id) => (string) $id)->toArray();
                                }
                                $items[] = [
                                    'item_id' => $item->id,
                                    'product_name' => trim(($item->productVariant?->product?->brand?->name ?? '') . ' ' . ($item->productVariant?->product?->name ?? '') . ' ' . ($item->productVariant?->name ?? '')),
                                    'qty_sent' => $item->qty_sent,
                                    'qty_already_received' => $item->qty_received,
                                    'qty_received' => $sisa, 
                                    'has_sn' => (bool) $item->product?->has_sn,
                                    'received_serials' => $serials,
                                ];
                            }
                            return ['received_items' => $items];
                        })
                        ->schema([
                            \Filament\Forms\Components\Repeater::make('received_items')
                                ->label('Verifikasi Barang Diterima')
                                ->addable(false)
                                ->deletable(false)
                                ->columns(5)
                                ->schema([
                                    \Filament\Forms\Components\Hidden::make('item_id'),
                                    \Filament\Forms\Components\Hidden::make('has_sn'),
                                    \Filament\Forms\Components\TextInput::make('product_name')
                                        ->label('Produk')
                                        ->disabled()
                                        ->columnSpan(2),
                                    \Filament\Forms\Components\TextInput::make('qty_sent')
                                        ->label('Total Dikirim')
                                        ->disabled()
                                        ->columnSpan(1),
                                    \Filament\Forms\Components\TextInput::make('qty_already_received')
                                        ->label('Sdh Diterima')
                                        ->disabled()
                                        ->columnSpan(1),
                                    \Filament\Forms\Components\TextInput::make('qty_received')
                                        ->label('Terima Saat Ini')
                                        ->numeric()
                                        ->required()
                                        ->readOnly(fn ($get) => filter_var($get('has_sn'), FILTER_VALIDATE_BOOLEAN))
                                        ->maxValue(fn ($get) => $get('qty_sent') - $get('qty_already_received'))
                                        ->minValue(0)
                                        ->columnSpan(1),
                                    \Filament\Forms\Components\CheckboxList::make('received_serials')
                                        ->label('Pilih Serial Number yang Baru Sampai')
                                        ->options(function ($get) {
                                            $item = \App\Models\Tenant\StockTransferItem::with('itemSerials.productSerial')->find($get('item_id'));
                                            if (!$item || !$item->itemSerials) return [];
                                            return $item->itemSerials->filter(function ($serialRecord) {
                                                return $serialRecord->productSerial && $serialRecord->productSerial->status === 'transfered';
                                            })->mapWithKeys(function ($serialRecord) {
                                                return [$serialRecord->productSerial->id => $serialRecord->productSerial->serial_number];
                                            })->toArray();
                                        })
                                        ->visible(fn ($get) => filter_var($get('has_sn'), FILTER_VALIDATE_BOOLEAN))
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $set('qty_received', count($state));
                                        })
                                        ->columnSpanFull()
                                        ->columns(3),
                                ])
                        ])
                        ->action(function (array $data, $record) {
                            \Illuminate\Support\Facades\Log::info('Receive Action Data: ', $data);
                            \App\Filament\Tenant\Resources\StockTransfers\Actions\StockTransferActionStatus::receiveAction($record, $data['received_items']);
                        }),

                    Action::make('cancel')
                        ->label('Batalkan')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => in_array($record->status, ['draft', 'in_transit']) && ($record->from_branch_id == session('current_branch_id') || Auth::user()?->hasRole(['admin', 'owner', 'super_admin'])))
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'canceled',
                                'canceled_by' => Auth::id(),
                                'canceled_at' => now(),
                            ]);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
