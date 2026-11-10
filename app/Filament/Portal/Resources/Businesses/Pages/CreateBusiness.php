<?php

namespace App\Filament\Portal\Resources\Businesses\Pages;

use App\Filament\Portal\Resources\Businesses\BusinessResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBusiness extends CreateRecord
{
    protected static string $resource = BusinessResource::class;


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function mount(): void
    {
        parent::mount();

        $user = \Illuminate\Support\Facades\Auth::user();
        $activeSubscription = $user->getActiveSubscription();

        // dd($activeSubscription);

        if (!$activeSubscription) {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('Langganan Diperlukan')
                ->body('Silakan pilih paket langganan untuk dapat membuat bisnis baru.')
                ->send();

            redirect(\App\Filament\Portal\Pages\Billing::getUrl());
            return;
        }

        $maxTenants = $activeSubscription->plan->getFeatureValue(\App\Enums\TenantFeature::TENANT) ?? 0;
        // dd($activeSubscription->plan);
        if ($user->tenants()->count() >= $maxTenants) {
            Notification::make()
                ->warning()
                ->title('Kuota Maksimal')
                ->body('Kuota toko Anda sudah penuh. Silakan upgrade paket langganan Anda.')
                ->send();

            redirect(\App\Filament\Portal\Pages\Billing::getUrl());
            return;
        }
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            // 1. Ambil user yang sedang login
            $user = Auth::user();
            // 2. Cari subscription aktif milik user ini
            $activeSubscription = $user->getActiveSubscription();
            if (!$activeSubscription) {
                // Jika Anda ingin mencegah user membuat bisnis tanpa langganan aktif:
                throw new \Exception("Anda harus memiliki langganan aktif sebelum membuat Bisnis/Tenant.");
            }
            // Sisipkan ID subscription ke data tenant yang akan dibuat
            $data['subscription_id'] = $activeSubscription->id;

            return \App\Services\TenantManagerService::createTenant($data);
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Gagal menyimpan')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw new \Filament\Support\Exceptions\Halt();
        }
    }

    protected function afterCreate(): void
    {
        $tenant = $this->record;

        // 1. Tampilkan notifikasi proses migrasi
        \Filament\Notifications\Notification::make()
            ->title('Sedang memproses pembuatan toko...')
            ->body("Toko {$tenant->business_name} sedang disiapkan di background, mohon tunggu beberapa saat.")
            ->info()
            ->send();
    }
}
