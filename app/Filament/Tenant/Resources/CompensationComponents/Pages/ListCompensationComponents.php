<?php

namespace App\Filament\Tenant\Resources\CompensationComponents\Pages;

use App\Filament\Tenant\Resources\CompensationComponents\CompensationComponentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompensationComponents extends ListRecords
{
    protected static string $resource = CompensationComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
