<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Filament\Support\Facades\FilamentColor::register([
            'danger' => \Filament\Support\Colors\Color::Red,
            'gray' => \Filament\Support\Colors\Color::Zinc,
            'info' => \Filament\Support\Colors\Color::Blue,
            'primary' => \Filament\Support\Colors\Color::Amber,
            'success' => \Filament\Support\Colors\Color::Green,
            'warning' => \Filament\Support\Colors\Color::Amber,
        ]);
        Relation::morphMap([
            'plan' => \App\Models\Plan::class,
            'addon' => \App\Models\Addon::class,
            'invoice' => \App\Models\Invoice::class,
            'subscription' => \App\Models\Subscription::class,
            'subscription_addon' => \App\Models\SubscriptionAddon::class,
            'topup' => \App\Models\Wallet::class,
        ]);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });
        //Customize auth guard
        // \Illuminate\Support\Facades\Event::listen(\Filament\Events\ServingFilament::class, function ()
        // {
        //     if (\Filament\Facades\Filament::getCurrentPanel()?->getId() == 'tenant') {
        //         \Illuminate\Support\Facades\Auth::shouldUse('tenant');
        //     }
        //     // else {
        //     //     \Illuminate\Support\Facades\Auth::shouldUse('web');
        //     // }
        // });

        // Jika domain yang diakses adalah central domain, return null (bypass tenancy)
        \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::$onFail = function ($exception, $request, $next) {
            if (in_array($request->getHost(), config('tenancy.central_domains'))) {
                return $next($request);
            }
            throw $exception;
        };
    }
}
