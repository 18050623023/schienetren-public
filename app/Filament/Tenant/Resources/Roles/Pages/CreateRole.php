<?php

namespace App\Filament\Tenant\Resources\Roles\Pages;

use App\Filament\Tenant\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;


    protected function afterCreate(): void
    {
        $this->syncPermissions();
    }

    private function syncPermissions()
    {
        $selected = collect($this->data['permissions'] ?? [])
            ->flatten()
            ->filter()
            ->values()
            ->toArray();

        $this->record->syncPermissions($selected);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
