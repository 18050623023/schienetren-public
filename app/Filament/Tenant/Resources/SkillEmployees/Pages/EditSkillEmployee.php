<?php

namespace App\Filament\Tenant\Resources\SkillEmployees\Pages;

use App\Filament\Tenant\Resources\SkillEmployees\SkillEmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditSkillEmployee extends EditRecord
{
    protected static string $resource = SkillEmployeeResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         DeleteAction::make(),
    //     ];
    // }

    #[Override]
    public function mutateFormDataBeforeFill(array $data): array
    {
        // dd($data);
        return parent::mutateFormDataBeforeFill($data);
    }
}
