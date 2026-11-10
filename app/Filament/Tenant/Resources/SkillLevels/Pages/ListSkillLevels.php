<?php

namespace App\Filament\Tenant\Resources\SkillLevels\Pages;

use App\Filament\Tenant\Resources\SkillLevels\SkillLevelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSkillLevels extends ListRecords
{
    protected static string $resource = SkillLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
