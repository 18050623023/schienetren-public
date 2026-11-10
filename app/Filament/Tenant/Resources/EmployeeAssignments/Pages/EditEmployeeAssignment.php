<?php

namespace App\Filament\Tenant\Resources\EmployeeAssignments\Pages;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use App\Filament\Tenant\Resources\EmployeeAssignments\EmployeeAssignmentResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeAssignment extends EditRecord
{
    protected static string $resource = EmployeeAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancelEdit')
                ->label('Cancel Edit')
                ->color('gray')
                ->icon('heroicon-o-x-circle')
                ->action(fn () => redirect($this->getResource()::getUrl('view', ['record' => $this->record]))),
            DeleteAction::make()
                ->requiresConfirmation(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
