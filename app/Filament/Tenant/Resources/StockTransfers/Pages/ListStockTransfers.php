<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Pages;

use App\Filament\Tenant\Resources\StockTransfers\StockTransferResource;
use App\Models\Tenant\Branch;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListStockTransfers extends ListRecords
{
    protected static string $resource = StockTransferResource::class;

    public function getSubheading(): string | Htmlable | null
    {
        if (Branch::where('is_active', true)->count() <= 1) {
            return new HtmlString(
                '<div class="p-3 text-sm text-amber-700 bg-amber-50 rounded-lg border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">' .
                '⚠️ <strong>Perhatian:</strong> Mutasi stok membutuhkan minimal 2 cabang aktif. Tombol buat mutasi dinonaktifkan hingga Anda menambahkan atau mengaktifkan cabang baru.' .
                '</div>'
            );
        }

        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
