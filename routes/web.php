<?php

use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains', []) as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            $rawPlans = \App\Models\Plan::with('planFeatures.feature')->where('is_active', true)->get();
            $plans = \App\Services\BillingService::formatPlansForCards($rawPlans, false);
            return view('welcome', compact('plans'));
        });
    });
}

Route::redirect('/login', '/portal/login')->name('login');
Route::redirect('/register', '/portal/register')->name('register');
Route::get('/portal/logout', fn() => redirect('/portal/login'));
Route::get('/admin/logout', fn() => redirect('/admin/login'));


if (app()->isLocal()) {
    Route::get('/debug-db', function () {
        $subdomain = request()->getHost();

        // Coba manual init tenancy
        // if (str_contains($subdomain, 'tenant')) { // Sesuaikan logika subdomain Anda
        //      // Ambil tenant sembarang untuk test
        //      $tenant = \App\Models\Tenant::first();
        //      tenancy()->initialize($tenant);
        // }

        $default = config('database.default');
        $userConnection = (new \App\Models\Tenant\User())->getConnection()->getName();
        $con = \Illuminate\Support\Facades\DB::connection()->getName();

        // Coba Query
        try {
            // $count = \App\Models\User::count();
            $dbName = Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        } catch (\Exception $e) {
            $dbName = $e->getMessage();
        }

        dd([
            'Subdomain' => $subdomain,
            'Tenancy Initialized?' => tenancy()->initialized,
            'Laravel Config Default' => $default,
            'User Model Connection' => $con,
            'Actual DB Connected' => $dbName,
        ]);
    });
}
