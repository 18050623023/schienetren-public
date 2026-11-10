<?php

namespace App\Filament\Tenant\Resources\FinanceJournals\Pages;

use App\Filament\Tenant\Resources\FinanceJournals\FinanceJournalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFinanceJournals extends ListRecords
{
    protected static string $resource = FinanceJournalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
