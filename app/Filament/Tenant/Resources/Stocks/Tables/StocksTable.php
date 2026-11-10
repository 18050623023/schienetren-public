<?php

namespace App\Filament\Tenant\Resources\Stocks\Tables;

use App\Filament\Tenant\Resources\Stocks\Actions\AdjustStockAction;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction(fn ($record) => $record->product->has_sn ? 'viewSerials' : null)
            ->reorderable()
            ->query(
                $table->getQuery()
                    ->when(session('current_branch_id'),
                        fn ($q, $branchId) => $q->where('branch_id', $branchId)
                    )
            )
            ->headerActions([
                AdjustStockAction::makeHeaderAction(),
            ])
            ->contentGrid([
                'md' => 3,
                'xl' => 4,
            ])
            ->columns([
                Stack::make([
                    TextColumn::make('product.code')
                        ->searchable()
                        ->state(fn ($record) =>
                            // dd($record)
                            $record->product->code
                        ),
                    TextColumn::make('branch.name')
                        ->searchable()
                        ->visible(fn ($record) => $record?->branch_id != session('current_branch_id')),
                    TextColumn::make('product.name')
                        ->searchable()
                        ->state(fn ($record) =>
                                // dd($record)
                                $record->product->brand->name.' ' .
                                $record->product->name.' '.
                                $record->productVariant->name
                            ),
                    // TextColumn::make('productVariant.name'),
                    TextColumn::make('stock')
                    ->state(fn ($record) =>
                            // dd($record)
                            'Stock: '.$record->stock.' '.$record->product->unit->name
                        ),
                ])
            ])
            ->filters([
                //
            ])
            ->recordActions([
                AdjustStockAction::make(),

                ViewAction::make('viewSerials')
                    ->label('View Serials')
                    ->mountUsing(function ($record) {
                            if ($record->product->has_sn && $record->productVariant->productSerial->isEmpty()) {
                                Notification::make()
                                    ->title('Serial kosong')
                                    ->body('Tidak ada serial numbers, Harap isi serial numbers terlebih dahulu di Stock Opname (Audit)')
                                    ->danger()
                                    ->actions([
                                            Action::make('audit')
                                                ->label('Audit')
                                                ->url(route('filament.tenant.resources.stock-opnames.audit'))
                                                ->button(),
                                        ])
                                    ->send();
                            }
                        })

                    // ->modalHeading('Product Serial Numbers')
                    ->modalHeading(fn ($record) =>
                            $record->productVariant->productSerial->isEmpty()
                                ? '⚠️ Product Serial Numbers (Kosong)'
                                : 'Product Serial Numbers'
                        )

                    ->modalWidth('4xl')
                    ->visible(fn ($record) => $record->product->has_sn == true)
                    ->infolist([
                        Section::make('Product Info')
                            ->columns(3)
                            ->schema([
                                // dd($record),
                                TextEntry::make('product.code'),
                                TextEntry::make('product.name')
                                    ->label('Product')
                                    ->state(fn ($record) =>
                                        // dd($record)
                                        $record->product->brand->name.' ' .
                                        $record->product->name.' '.
                                        $record->productVariant->name
                                    ),
                                TextEntry::make('branch.name')
                                // TextEntry::make('product.has_sn')
                                //     ->label('Has SN')
                                //     ->badge()
                                //     // ->visible(fn ($record) => dd($record))
                                //     ->color(fn ($state) => $state ? 'success' : 'gray'),
                            ]),

                        ViewEntry::make('serials')
                            ->view('filament.tenant.components.serial-modal')
                            ->visible(fn ($record) => $record->product->has_sn)
                        // Section::make('Serial Numbers')
                        //     ->visible(fn ($record) => $record->product->has_sn)
                        //     ->schema([
                        //         // Repeater::make('productVariant.productSerials')
                        //         //     ->relationship()
                        //         //     ->schema([
                        //         //         TextEntry::make('serial_number'),
                        //         //     ])
                        //         //     ->columns(2),

                        //         TextEntry::make('serials')
                        //             ->label('Serial Numbers')
                        //             ->state(fn ($record) =>
                        //                 // dd($record->productVariant->productSerial),
                        //                 $record->productVariant
                        //                     ->productSerial
                        //                     ->map(fn ($serial) =>
                        //                         "{$serial->serial_number} ({$serial->status})"
                        //                     )
                        //                     ->toArray()
                        //             )
                        //             ->listWithLineBreaks()
                        //         // TextEntry::make('productVariant.productSerials.serial_number')
                        //         //     ->listWithLineBreaks()
                        //         //     ->label('Serial Numbers'),
                        //     ]),
                    ]),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
