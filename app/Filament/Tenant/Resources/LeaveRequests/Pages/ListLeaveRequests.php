<?php

namespace App\Filament\Tenant\Resources\LeaveRequests\Pages;

use App\Filament\Tenant\Resources\LeaveRequests\LeaveRequestResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListLeaveRequests extends ListRecords
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            // Action::make('changeYear')
            // ->label('Pilih Tahun')
            // ->form([
            //     Select::make('year')
            //         ->options([
            //             2025 => '2025',
            //             2026 => '2026',
            //         ])
            //         ->required(),
            // ])
            // ->action(function (array $data) {
            //     // proses
            // }),
        ];
    }
}
