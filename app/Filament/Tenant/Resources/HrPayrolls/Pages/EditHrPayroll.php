<?php

namespace App\Filament\Tenant\Resources\HrPayrolls\Pages;

use App\Filament\Tenant\Resources\HrPayrolls\HrPayrollResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditHrPayroll extends EditRecord
{
    protected static string $resource = HrPayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
