<?php

namespace App\Filament\Tenant\Resources\SkillSpecializations\Pages;

use App\Filament\Tenant\Resources\SkillSpecializations\SkillSpecializationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSkillSpecialization extends EditRecord
{
    protected static string $resource = SkillSpecializationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
