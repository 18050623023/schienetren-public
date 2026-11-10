<?php

namespace App\Filament\Tenant\Resources\FinanceJournals\Pages;

use App\Filament\Tenant\Resources\FinanceJournals\FinanceJournalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditFinanceJournal extends EditRecord
{
    protected static string $resource = FinanceJournalResource::class;

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
