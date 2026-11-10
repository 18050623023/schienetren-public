<?php

namespace App\Filament\Tenant\Resources\EmployeeAssignments\Pages;

use App\Filament\Tenant\Resources\EmployeeAssignments\EmployeeAssignmentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeAssignment extends CreateRecord
{
    protected static string $resource = EmployeeAssignmentResource::class;
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     dd($data);

    //     // return $data;
    // }


    // protected function getCreateFormAction(): Action
    // {
    //     // return parent::getCreateFormAction()
    //     //     ->before(function () {
    //     //         $this->validate();
    //     //         $actionId = str('fi-')
    //     //             ->append($this->getId())
    //     //             ->append('-action-0')
    //     //             ->toString();

    //     //         $this->dispatch('close-modal', id: $actionId);
    //     //     })
    //     //     ->submit(null)
    //     //     ->requiresConfirmation()
    //     //     ->action(function () {
    //     //         $this->validate();

    //     //         $this->create();
    //     //     });
    //     return Action::make('submit')
    //         // ->icon('heroicon-s-shopping-cart')
    //         ->label('Submit Stock Opname')
    //         ->requiresConfirmation()
    //         ->modalHeading('Confirm Initial Stock')
    //         ->modalDescription('test coba')
    //         ->modalCancelActionLabel('Cancel')
    //         ->modalSubmitActionLabel('Confirm')
    //         ->action(fn (CreateEmployeeAssignment $livewire) => $livewire->create())
    //         // ->action(function (CreateStockOpname $livewire) {
    //         //     // $livewire->validate(); // validasi form
    //         //     $validator = Validator::make(
    //         //         $livewire->form->getState(),
    //         //         $livewire->getRules()
    //         //     );

    //         //     if ($validator->fails()) {

    //         //         // inject error ke form
    //         //         $livewire->setErrorBag($validator->errors());

    //         //         // kasih notifikasi (optional)
    //         //         Notification::make()
    //         //             ->title('Validasi gagal')
    //         //             ->danger()
    //         //             ->send();

    //         //         return; // ⬅️ stop tanpa exception
    //         //     }
    //         //     $livewire->create();
    //         // })
    //         ->keyBindings(['mod+s']);
    // }

}
