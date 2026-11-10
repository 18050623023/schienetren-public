<?php

namespace App\Filament\Admin\Resources\Roles\Pages;

use App\Filament\Admin\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;


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
}
