<?php

namespace App\Filament\Tenant\Resources\Roles\Pages;

use App\Filament\Tenant\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }



    // Dipanggil setelah simpan untuk sinkronisasi permissions
    protected function afterSave(): void
    {
        // $this->syncPermissions();
        $role = $this->record;

        $selectedPermissions = collect($this->form->getState()['permissions'] ?? []);

        // Hapus & simpan ulang permission secara benar menggunakan Spatie
        $role->syncPermissions($selectedPermissions);

        // WAJIB: clear cache Spatie
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    // (opsional) Kalau Anda butuh mutate saat fill (biasanya tidak perlu karena afterStateHydrated di atas sudah mengisi)
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $permissions = $this->record->permissions->pluck('name')->toArray();
        $grouped = [];

        foreach ($permissions as $permission) {
            if (! str_contains($permission, ':')) {
                continue;
            }
            [$action, $model] = explode(':', $permission, 2);
            $grouped[$model][] = $permission;
        }

        $data['permissions'] = $grouped;

        return $data;
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Jangan biarkan filament update kolom "permissions"
        unset($data['permissions']);

        return $data;
    }
}
