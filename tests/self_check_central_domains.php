<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Running self-check for central_domains configuration...\n";

// 1. Check config values
$centralDomains = config('tenancy.central_domains');
$mainDomain = config('tenancy.main_domain');

assert(is_array($centralDomains), "central_domains must be an array");
assert(in_array('127.0.0.1', $centralDomains), "127.0.0.1 must be in central_domains");
assert(in_array('localhost', $centralDomains), "localhost must be in central_domains");
assert(in_array($mainDomain, $centralDomains), "main_domain ($mainDomain) must be in central_domains");
echo "[PASS] Config check: main_domain = {$mainDomain}, central_domains = " . implode(', ', $centralDomains) . "\n";

// 2. Check CustomDomainTenantResolver
$resolver = app(\App\Tenancy\CustomDomainTenantResolver::class);

// Central domain must throw TenantCouldNotBeIdentifiedOnDomainException
$caught = false;
try {
    $resolver->resolveWithoutCache($mainDomain);
} catch (\Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException $e) {
    $caught = true;
}
assert($caught, "Resolving central domain ($mainDomain) must throw TenantCouldNotBeIdentifiedOnDomainException");
echo "[PASS] CustomDomainTenantResolver correctly identifies main_domain as central.\n";

// Localhost must also throw TenantCouldNotBeIdentifiedOnDomainException
$caughtLocalhost = false;
try {
    $resolver->resolveWithoutCache('localhost');
} catch (\Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException $e) {
    $caughtLocalhost = true;
}
assert($caughtLocalhost, "Resolving localhost must throw TenantCouldNotBeIdentifiedOnDomainException");
echo "[PASS] CustomDomainTenantResolver correctly identifies localhost as central.\n";

// 127.0.0.1 must also throw TenantCouldNotBeIdentifiedOnDomainException
$caughtIp = false;
try {
    $resolver->resolveWithoutCache('127.0.0.1');
} catch (\Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException $e) {
    $caughtIp = true;
}
assert($caughtIp, "Resolving 127.0.0.1 must throw TenantCouldNotBeIdentifiedOnDomainException");
echo "[PASS] CustomDomainTenantResolver correctly identifies 127.0.0.1 as central.\n";

echo "ALL CENTRAL DOMAIN CHECKS PASSED!\n";
