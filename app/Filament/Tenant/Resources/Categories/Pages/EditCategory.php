<?php

namespace App\Filament\Tenant\Resources\Categories\Pages;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use App\Filament\Tenant\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
