<?php

namespace App\Filament\Tenant\Resources\Compensation\Pages;

use App\Filament\Tenant\Resources\Compensation\CompensationResource;
use App\Filament\Tenant\Resources\CompensationComponents\CompensationComponentResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompensation extends ListRecords
{
    protected static string $resource = CompensationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('compensationComponents')
                ->label('Manage Compensation Components')
                ->icon('heroicon-o-chart-bar')
                ->url(CompensationComponentResource::getUrl()),

            CreateAction::make(),
        ];
    }

}
