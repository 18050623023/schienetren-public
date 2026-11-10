<?php

namespace App\Filament\Tenant\Resources\CompensationComponents\Pages;

use App\Filament\Tenant\Resources\CompensationComponents\CompensationComponentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCompensationComponent extends EditRecord
{
    protected static string $resource = CompensationComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
