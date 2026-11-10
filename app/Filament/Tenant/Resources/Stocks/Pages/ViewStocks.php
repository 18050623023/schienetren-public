<?php

namespace App\Filament\Tenant\Resources\Stocks\Pages;

use App\Filament\Tenant\Resources\Stocks\StockResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;

class ViewStocks extends ViewRecord
{
    protected static string $resource = StockResource::class;

    protected function getInfolistSchema(): array
    {
        return [
            Section::make('Product Information')
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('sku'),
                    TextEntry::make('product.has_sn')
                        ->label('Has Serial Number')
                        ->badge()
                        ->color(fn ($state) => $state ? 'success' : 'gray'),
                ]),

            Section::make('Serial Numbers')
                ->visible(fn ($record) => $record->product->has_sn)
                ->schema([
                    TextEntry::make('serials.serial_number')
                        ->listWithLineBreaks(),
                ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
