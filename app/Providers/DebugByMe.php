<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class DebugByMe extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
         \Illuminate\Support\Facades\Event::listen(\Stancl\Tenancy\Events\TenancyInitialized::class, function ($event) {
            logger("DBase tenancy for tenant : " . $event->tenancy->tenant->database()->getName());
        });

        \Illuminate\Support\Facades\Event::listen(\Filament\Events\ServingFilament::class, function () {

            if (\Filament\Facades\Filament::getCurrentPanel()?->getId() == 'tenant') {
                \Illuminate\Support\Facades\Log::info('SERVING FILAMENT Tenant ON PANEL: ' . \Filament\Facades\Filament::getCurrentPanel()?->getId());
                \Illuminate\Support\Facades\Log::info('GUARD TENANT: ' . \Illuminate\Support\Facades\Auth::getDefaultDriver());
                \Illuminate\Support\Facades\Log::info('INFO DBASE TENANT: ' .DB::connection()->getDatabaseName());

            }else {
                \Illuminate\Support\Facades\Log::info('SERVING FILAMENT Admin ON PANEL: ' . \Filament\Facades\Filament::getCurrentPanel()?->getId());
                \Illuminate\Support\Facades\Log::info('GUARD admin: ' . \Illuminate\Support\Facades\Auth::getDefaultDriver());
                // \Illuminate\Support\Facades\Log::info('INFO DBASE ADMIN: ' . \Stancl\Tenancy\Tenancy::database()->getName());

            }


            // \Illuminate\Support\Facades\Log::info('DBase User: ' . ModelsUser::getConnection()->getName());
            // $subdomain = request()->getHost();
            // \Illuminate\Support\Facades\Log::info('host admin: ' . $subdomain);

            // $tenant = \App\Models\Tenant::find($subdomain)->first();
            // tenancy()->initialize($tenant);
            // if (str_contains($subdomain, 'tenant')) { // Sesuaikan logika subdomain Anda
                 // Ambil tenant sembarang untuk test
            // }

        });
    }
}
