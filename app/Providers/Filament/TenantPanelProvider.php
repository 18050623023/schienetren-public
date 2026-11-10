<?php

namespace App\Providers\Filament;

use App\Filament\Schemas\Components\BranchSwitcher;
use App\Filament\Tenant\Pages\Dashboard\Dashboard as PagesDashboard;
use App\Filament\Tenant\Pages\Auth\CustomEditProfile;
use App\Filament\Tenant\Widgets\WelcomeTenantWidget;
use Filament\Actions\Action;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Notifications\Livewire\Notifications;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\Facades\FilamentAsset;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Middleware\ScopeSessions;
use YousefAman\ModalRepeater\ModalRepeaterPlugin;

class TenantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

        // FilamentAsset::register([
        //     Css::make('component-css', resource_path('resources/css/component.css')),
        // ]);

        Notifications::alignment(Alignment::Center);
        Notifications::verticalAlignment(VerticalAlignment::Start);

        return $panel
            ->id('tenant')
            ->path('admin')
            // ->domain('{tenant}.'.env('TENANCY_CENTRAL_DOMAIN'))
            ->authGuard('tenant')
            ->login()
            // ->userMenu(position: UserMenuPosition::Sidebar)
            ->brandName(fn() => settingTenant('brand_name', 'Schienetren'))
            ->userMenuItems([
                'profile' => fn(Action $action) => $action->label(Auth::user()?->name),
            ])
            ->profile(CustomEditProfile::class, isSimple: false)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->assets([
                // Css::make('component', resource_path('/css/component.css')),
                Js::make('geofence-map', resource_path('/js/geofence-map.js')),
            ])
            // ->viteTheme('resources/css/app.css')
            // ->viteTheme('resources/css/filament/tenant/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn () => Blade::render('@livewire(\'branch-switcher\')'),// Filament::renderComponent(BranchSwitcher::class),
            )
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => Blade::render('@vite([\'resources/css/app.css\'])'),
            )
            ->discoverResources(in: app_path('Filament/Tenant/Resources'), for: 'App\Filament\Tenant\Resources')
            ->discoverPages(in: app_path('Filament/Tenant/Pages'), for: 'App\Filament\Tenant\Pages')
            ->pages([
                // Dashboard::class,
                PagesDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Tenant/Widgets'), for: 'App\Filament\Tenant\Widgets')
            // ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->widgets([
                AccountWidget::class,
                WelcomeTenantWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ScopeSessions::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,

            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\CheckAttendanceMiddleware::class,
            ])
            ->tenantMiddleware([
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
                ScopeSessions::class,
            ])
            ->plugin(ModalRepeaterPlugin::make())
            ->plugin(\Relaticle\Flowforge\FlowforgePlugin::make());
            // ->plugin(
            // FilamentFullCalendarPlugin::make()
            //     // ->schedulerLicenseKey()
            //     ->selectable()
            // ->editable()
            // ->timezone('Asia/Jakarta')
            // ->locale('')
            // ->plugins()
            // ->config()
            // )
        ;
    }
}
