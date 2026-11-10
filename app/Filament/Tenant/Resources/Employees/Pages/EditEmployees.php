<?php

namespace App\Filament\Tenant\Resources\Employees\Pages;

use App\Filament\Tenant\Resources\Employees\EmployeesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployees extends EditRecord
{
    protected static string $resource = EmployeesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
