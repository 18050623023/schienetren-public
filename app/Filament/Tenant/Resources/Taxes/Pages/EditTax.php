<?php

namespace App\Filament\Tenant\Resources\Taxes\Pages;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use App\Filament\Tenant\Resources\Taxes\TaxResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTax extends EditRecord
{
    protected static string $resource = TaxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
