<?php

namespace App\Filament\Tenant\Resources\SkillEmployees\Pages;

use App\Filament\Tenant\Resources\SkillEmployees\SkillEmployeeResource;
use App\Filament\Tenant\Resources\SkillLevels\SkillLevelResource;
use App\Filament\Tenant\Resources\SkillSpecializations\SkillSpecializationResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSkillEmployees extends ListRecords
{
    protected static string $resource = SkillEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('specializations')
                ->label('Manage Specializations')
                ->icon('heroicon-o-wrench-screwdriver')
                ->url(SkillSpecializationResource::getUrl()),

            Action::make('levels')
                ->label('Manage Levels')
                ->icon('heroicon-o-chart-bar')
                ->url(SkillLevelResource::getUrl()),
            CreateAction::make(),
        ];
    }
}
