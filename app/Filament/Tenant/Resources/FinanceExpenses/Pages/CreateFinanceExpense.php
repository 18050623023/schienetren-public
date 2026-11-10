<?php

namespace App\Filament\Tenant\Resources\FinanceExpenses\Pages;

use App\Filament\Tenant\Resources\FinanceExpenses\FinanceExpenseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinanceExpense extends CreateRecord
{
    protected static string $resource = FinanceExpenseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
