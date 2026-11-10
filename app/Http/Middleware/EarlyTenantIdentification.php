<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class EarlyTenantIdentification
{
    protected $resolver;

    public function __construct(DomainTenantResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function handle($request, Closure $next)
    {
        if (! tenancy()->initialized) {
            try {
                $tenant = $this->resolver->resolve($request->getHost());
                tenancy()->initialize($tenant);
            } catch (\Exception $e) {
                // Silently ignore if tenant is not found (e.g. on central domains)
                // This allows the global middleware to continue and StartSession to use the central DB.
            }
        }

        return $next($request);
    }
}
