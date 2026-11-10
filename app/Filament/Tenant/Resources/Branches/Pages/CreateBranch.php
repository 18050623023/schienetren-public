<?php

namespace App\Filament\Tenant\Resources\Branches\Pages;

use App\Filament\Tenant\Resources\Branches\BranchResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // $new = app(LeavePolicyService::class)
        //     ->calculateQuota($data['employee_id'], $data['leave_type_id'], $data['start_date'], $data['end_date']);
        // dd($periodStart);
        // dd($data['location']->lng);
        // return $data;

        // $data['location'] = $data['location']->toArray();
        return $data;
    }
}
