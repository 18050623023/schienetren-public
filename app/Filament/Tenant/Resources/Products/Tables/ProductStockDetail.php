<?php

namespace App\Filament\Tenant\Resources\Products\Tables;
// use Filament\Tables\Actions\RestoreBulkAction;
// use Filament\Tables\Actions\ForceDeleteBulkAction;
// use Filament\Tables\Actions\RestoreAction;
// use Filament\Tables\Actions\ForceDeleteAction;

use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class ProductStockDetail
{
    public static function make(): array
    {
        return [
            Section::make('Product Information')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('code')->label('Code'),
                        TextEntry::make('name')->label('Name'),
                        TextEntry::make('has_sn')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                    ]),
                ]),

            Section::make('Stock per Variant & Branch')
                ->schema([
                    RepeatableEntry::make('variants')
                        ->label('')
                        ->columns(6)
                        ->schema([

                            TextEntry::make('name')
                                ->label('Variant')
                                ->columnSpan(1)
                                ->weight('bold'),

                            RepeatableEntry::make('stocks')
                                ->label('')
                                ->columnSpan(5)
                                ->schema([
                                    Grid::make(3)->schema([

                                        TextEntry::make('branch.name')
                                            ->label('Cabang'),

                                        TextEntry::make('stock')
                                            ->label('Stock')
                                            ->state(fn ($record) => $record->stock.' '.$record->product->unit->name),

                                        TextEntry::make('updated_at')
                                            ->label('Update')
                                            ->since(),

                                        TextEntry::make('serial_summary')
                                            ->label('Serial Numbers')
                                            ->visible(fn ($record) => $record->product->has_sn)
                                            ->columnSpan(3)
                                            ->state(function ($record) {
                                                $count = \App\Models\Tenant\ProductSerial::query()->where('product_variant_id', $record->product_variant_id)
                                                    ->where('branch_id', $record->branch_id)
                                                    ->count('id');
                                                return $count . ' Item(s)';
                                            })
                                            ->suffixAction(
                                                Action::make('view_all_serials')
                                                    ->label('Lihat Detail')
                                                    ->icon('heroicon-m-eye')
                                                    ->modalHeading('Daftar Serial Number')
                                                    ->modalSubmitAction(false)
                                                    ->modalCancelActionLabel('Tutup')
                                                    ->modalContent(fn ($record) => view('filament.tenant.components.serial-modal', ['stockId' => $record->id]))
                                            )
                                    ]),
                                ]),
                        ]),
                ]),
        ];
    }
}
?>
