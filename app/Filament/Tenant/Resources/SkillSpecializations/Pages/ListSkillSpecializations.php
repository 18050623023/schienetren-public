<?php

namespace App\Filament\Tenant\Resources\SkillSpecializations\Pages;

use App\Filament\Tenant\Resources\SkillSpecializations\SkillSpecializationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSkillSpecializations extends ListRecords
{
    protected static string $resource = SkillSpecializationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
