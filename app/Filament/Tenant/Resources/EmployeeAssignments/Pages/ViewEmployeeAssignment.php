<?php

namespace App\Filament\Tenant\Resources\EmployeeAssignments\Pages;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use App\Filament\Tenant\Resources\EmployeeAssignments\EmployeeAssignmentResource;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Support\ModalDescriptions;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ViewEmployeeAssignment extends ViewRecord
{
    protected static string $resource = EmployeeAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make(),
            ForceDeleteAction::make(),
            Action::make('editAssignment')
                ->icon('heroicon-s-pencil')
                ->requiresConfirmation()
                ->visible(fn() => Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['owner'])))
                ->modalDescription(new HtmlString(
                    'Perubahan sekecil apapun akan mempengaruhi data history absensi, <br>' .
                    'dan Demi Berjalannya sistem yang optimal lebih baik membuat data Assignment baru, <br>'.
                    'HARAP MENGERTI RESIKO NYA!!!'
                ))
                ->modalCancelActionLabel('Cancel')
                ->modalSubmitActionLabel('Confirm')
                ->modalHeading('Confirm edit')
                ->action(fn () => redirect($this->getResource()::getUrl('edit', ['record' => $this->record])))
                ,
            // EditAction::make()
            //     ->icon('heroicon-s-pencil')
            // ,
        ];
    }
}
