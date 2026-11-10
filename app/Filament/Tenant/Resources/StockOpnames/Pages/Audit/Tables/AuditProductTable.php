<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Tables;

use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Actions\AuditProductAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use App\Models\Tenant\Product;
use App\Models\Tenant\StockOpnameItem;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Support\AuditStockOpnameHelper;
use App\Models\Tenant\StockOpname;

class AuditProductTable
{
    public static function make(Table $table, int $branchId, ?StockOpname $stockOpname ): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('type', 'product')
                    ->whereHas('stocks', fn ($q) =>
                        $q->where('branch_id', $branchId)
                    )
            )
            ->contentGrid([
                'md' => 3,
                'xl' => 4,
            ])
            ->columns([
                Stack::make([
                    TextColumn::make('name')
                        ->state(fn ($record) =>
                            // dd($record)
                            $record->brand->name.' ' . $record->name
                        ),

                    TextColumn::make('system_qty')
                        ->state(fn ($record) =>
                            'System Qty: ' .
                            $record->variants
                                ->flatMap(fn ($v) =>
                                    $v->stocks->where('branch_id', $branchId)
                                )
                                ->sum('stock')
                        ),

                    TextColumn::make('actual_qty')
                        ->state(function ($record) use ($branchId, $stockOpname) {

                            if($stockOpname && AuditStockOpnameHelper::isAudited($record, $stockOpname)) {
                                return 'Actual Qty: ' .
                                StockOpnameItem::where('branch_id', $branchId)
                                    ->where('product_id', $record->id)
                                    ->where('stock_opname_id', $stockOpname->id)
                                    ->sum('actual_qty');
                            };

                            $last = StockOpname::where('branch_id', $branchId)
                                    ->orderByDesc('posted_at')
                                    ->first();
                            return 'Actual Qty: ' .
                            StockOpnameItem::where('branch_id', $branchId)
                                ->where('product_id', $record->id)
                                ->where('stock_opname_id', $last?->id)
                                ->sum('actual_qty');
                        }),
                ]),
            ])
            ->recordActions([

                AuditProductAction::make(
                    branchId: $branchId,
                    stockOpname: $stockOpname,
                ),
            ]);
    }
}
