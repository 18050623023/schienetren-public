<?php

namespace App\Filament\Admin\Resources\Roles\Pages;

use App\Filament\Admin\Resources\Roles\RoleResource;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use PhpParser\Node\Expr\AssignOp\Mod;
use Illuminate\Support\Arr;

use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Spatie\Permission\Models\Permission;

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
