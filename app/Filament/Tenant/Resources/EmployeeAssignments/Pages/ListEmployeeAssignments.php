<?php

namespace App\Filament\Tenant\Resources\EmployeeAssignments\Pages;

use App\Filament\Tenant\Resources\EmployeeAssignments\EmployeeAssignmentResource;
use App\Models\Tenant\EmployeeAssignment;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ListEmployeeAssignments extends ListRecords
{
    protected static string $resource = EmployeeAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false)
                ->before(function (CreateAction $action, array $data) {
                    if (\App\Models\Tenant\EmployeeAssignment::hasActiveAssignment($data['employee_id'], $data['branch_id'])) {
                        \Filament\Notifications\Notification::make()
                            ->title('Assignment aktif sudah ada.')
                            ->danger()
                            ->send();
                        $action->halt();
                    }
                })
                // ->requiresConfirmation()
                // ->modalWidth(Width::FiveExtraLarge)
                // // ->modalHeading('Confirm Initial Stock')
                // // ->modalDescription('test coba')
                // ->modalCancelActionLabel('Cancel')
                // ->modalSubmitActionLabel('Coasdfnfirm')
                // ->action(fn (CreateEmployeeAssignment $livewire) => $livewire->create()),
        ];
    }
}
