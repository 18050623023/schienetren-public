<?php

namespace App\Filament\Tenant\Resources\FinanceExpenses\Pages;

use App\Filament\Tenant\Resources\FinanceExpenses\FinanceExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFinanceExpenses extends ListRecords
{
    protected static string $resource = FinanceExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
