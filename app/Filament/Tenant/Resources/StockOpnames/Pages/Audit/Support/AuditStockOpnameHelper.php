<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Support;

use App\Models\Tenant\StockOpname;

class AuditStockOpnameHelper
{
    public static function generateReference(): string
    {
        return 'SO-' .
            now()->format('ym') .
            '-' .
            str_pad(StockOpname::count() + 1, 4, '0', STR_PAD_LEFT);
    }

    public static function isAudited($record, $stockOpname): bool
    {
        return $stockOpname
            && $stockOpname->items()
                ->where('product_id', $record->id)
                ->exists();
    }

}
