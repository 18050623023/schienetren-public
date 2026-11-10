<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            require base_path('routes/tenant.php');
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(\App\Http\Middleware\EarlyTenantIdentification::class);
        // $middleware->prepend(\Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class);
        // $middleware->prepend(\Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class);
        // $middleware->prepend(\Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class);
        // $middleware->append(\Stancl\Tenancy\Middleware\PreventAccessFromUnwantedDomains::class);
        // $middleware->prepend(\Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain::class);
        
        // Percayai semua proxy (karena tersembunyi di belakang Cloudflare Tunnel yang aman)
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
