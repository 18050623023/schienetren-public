<?php

namespace App\Filament\Admin\Resources\Tenants\Pages;

use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Services\TenantManagerService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return TenantManagerService::createTenant($data);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw new Halt();
        }
    }

    protected function afterCreate(): void
    {
        $tenant = $this->record;

        // 🔔 Tampilkan notifikasi proses migrasi berjalan di background
        Notification::make()
            ->title('Sedang memproses setup tenant...')
            ->body("Tenant {$tenant->id} sedang disiapkan di background, mohon tunggu beberapa saat.")
            ->info()
            ->send();
    }
}
