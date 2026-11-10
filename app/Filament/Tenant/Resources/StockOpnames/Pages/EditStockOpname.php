<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages;

use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use App\Filament\Tenant\Resources\StockOpnames\StockOpnameResource;
use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\StockOpnameItemSerial;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

// TIDAK BERFUNGSI
class EditStockOpname extends EditRecord
{
    protected static string $resource = StockOpnameResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         DeleteAction::make(),
    // RestoreAction::make(),
    // ForceDeleteAction::make(),
    //     ];
    // }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $items = [];

        foreach ($this->record->items as $item) {

            $row = [
                'product_id'      => $item->product->id,
                'product_variant_id' => $item->product_variant_id,
                'qty'          => $item->actual_qty,
                'note'         => $item->notes,
                'has_sn'   => $item->product->has_sn,
                'serials'      => [],
            ];

            if ($item->product->has_sn) {
                //  $row['serials'] = ProductSerial::query()
                //                     ->where('product_variants_id', $item->product_variant_id)
                //                     ->where('branch_id', $this->record->branch_id)
                //                     ->pluck('serial_number')
                //                     ->map(fn ($s) => ['serial_number' => $s])
                $row['serials'] = StockOpnameItemSerial::query()
                    ->where('product_variant_id', $item->product_variant_id)
                    ->whereHas('stockOpnameItem', function ($q) {
                        $q->where('branch_id', $this->record->branch_id);
                    })
                    ->get(['serial_number'])
                    ->map(fn($s) => ['serial_number' => $s->serial_number])
                    ->toArray();
            }

            $items[] = $row;
        }

        $data['items'] = $items;

        return $data;
    }
}
