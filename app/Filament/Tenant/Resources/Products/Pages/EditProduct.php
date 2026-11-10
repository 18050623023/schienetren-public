<?php

namespace App\Filament\Tenant\Resources\Products\Pages;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use App\Filament\Tenant\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;


class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;

        if (! $record->has_variant) {
            $variant = $record->variants()->first();

            if ($variant) {
                $data['sku'] = $variant->sku;
                $data['purchase_price'] = $variant->purchase_price;
                $data['sale_price'] = $variant->sale_price;
            }
        }

        return $data;
    }

    protected function handleRecordUpdate($record, array $data): Model
    {
        $variantData = Arr::only($data, [
            'sku',
            'purchase_price',
            'sale_price',
        ]);

        Arr::forget($data, [
            'sku',
            'purchase_price',
            'sale_price',
        ]);

        $record->update($data);

        if (! $record->has_variant) {
            $variant = $record->variants()->firstOrCreate(
                ['name' => null],
                $variantData
            );

            $variant->update($variantData);
        }

        return $record;
    }


}
