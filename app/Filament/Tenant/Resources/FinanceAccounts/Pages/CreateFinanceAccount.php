<?php

namespace App\Filament\Tenant\Resources\FinanceAccounts\Pages;

use App\Filament\Tenant\Resources\FinanceAccounts\FinanceAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinanceAccount extends CreateRecord
{
    protected static string $resource = FinanceAccountResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
