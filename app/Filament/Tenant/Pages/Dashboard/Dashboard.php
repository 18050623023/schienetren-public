<?php

namespace App\Filament\Tenant\Pages\Dashboard;

use BackedEnum;
use Filament\Pages\Dashboard as PagesDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends PagesDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    // protected string $view = 'filament.tenant.pages.dashboard.index';
    
    protected static bool $shouldRegisterNavigation = false;

    public function mount()
    {
        $user = Auth::user();

        if ($user->hasRole(settingTenant('tenant_bypass_roles', ['owner']))) {
            return redirect()->to(OwnerDashboard::getUrl());
        }
        
        if ($user->hasRole(settingTenant('tenant_cs_roles', ['customer_service']))) {
            return redirect()->to(CsDashboard::getUrl());
        }

        if ($user->hasRole(settingTenant('tenant_technical_roles', ['teknisi', 'technician']))) {
            return redirect()->to(TechnicianDashboard::getUrl());
        }

        return redirect()->to(OwnerDashboard::getUrl());
    }
}
