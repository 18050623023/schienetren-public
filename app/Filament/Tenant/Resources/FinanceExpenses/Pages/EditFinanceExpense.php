<?php

namespace App\Filament\Tenant\Resources\FinanceExpenses\Pages;

use App\Filament\Tenant\Resources\FinanceExpenses\FinanceExpenseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditFinanceExpense extends EditRecord
{
    protected static string $resource = FinanceExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
