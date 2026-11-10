<?php

namespace App\Filament\Tenant\Resources\Products\Pages;

use App\Filament\Tenant\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    
    protected function handleRecordCreation(array $data): Model
    {
        // dd($data);
        // Ambil input default variant jika has_variant = false
        $defaultSku   = $data['sku'] ?? null;
        $defaultPurchasePrice = $data['purchase_price'] ?? 0;
        $defaultPrice = $data['sale_price'] ?? 0;
        // $defaultStock = $data['stock'] ?? 0;

        // Hapus field non-table dari data products
        unset($data['sku'], $data['purchase_price'], $data['sale_price']);

        // Buat product
        $product = static::getModel()::create($data);

        // Jika NON-VARIANT → auto buat variant default
        // if ($product->has_variant === false) {
        //     $product->variants()->create([
        //         'name' => null,
        //         'sku'          => $defaultSku,
        //         'purchase_price'        => $defaultPurchasePrice,
        //         'sale_price'        => $defaultPrice,
        //     ]);
        // }

        return $product;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
