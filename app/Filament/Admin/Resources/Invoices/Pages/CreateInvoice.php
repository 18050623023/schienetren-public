<?php

namespace App\Filament\Admin\Resources\Invoices\Pages;

use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    // Contoh logika di CreateInvoice.php
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $mergedItems = [];
        foreach ($data['items'] as $item) {
            // Buat kunci unik gabungan tipe dan ID
            $key = $item['item_type'] . '_' . $item['item_id'];

            if (isset($mergedItems[$key])) {
                // Jika sudah ada, tambahkan qty nya
                $mergedItems[$key]['qty'] += $item['qty'];
                // Boleh juga menjumlahkan price jika perlu
            } else {
                // Jika belum ada, masukkan ke array
                $mergedItems[$key] = $item;
            }
        }

        // Timpa items yang lama dengan yang sudah di-merger (index harus di-reset)
        $data['items'] = array_values($mergedItems);

        return $data;
    }
}
