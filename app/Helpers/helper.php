<?php

use Illuminate\Support\Facades\DB;

if (! function_exists('tenant_model')) {
    function tenant_model(string $class, string $guard = 'web')
    {
        // Cek apakah tenancy sedang aktif
        if ($guard == 'tenant' || tenant()) {
            $tenantClass = "App\\Models\\Tenant\\{$class}";

            if (class_exists($tenantClass)) {
                return new $tenantClass;
            }
        }

        // Jika tidak aktif tenancy, atau kelas tenant tidak ada
        $centralClass = "App\\Models\\{$class}";
        return new $centralClass;
    }
}

if (! function_exists('tenancheckTenantDatabaset')) {
    function checkTenantDatabase($id)
    {
        $dbName = 'tenant__' . $id;

        $databases = DB::select("SHOW DATABASES");

        foreach ($databases as $db) {
            if ($db->Database === $dbName) {
                return true;
            }
        }

        return false;
    }
}

// get tenant settings
if (! function_exists('settingTenant')) {
    function settingTenant($key, $default = null)
    {
        static $settingsCache = [];
        
        if (array_key_exists($key, $settingsCache)) {
            return $settingsCache[$key] ?? $default;
        }

        $setting = \App\Models\Tenant\Setting::where('key', $key)->first();
        $settingsCache[$key] = $setting ? $setting->value : null;

        return $settingsCache[$key] ?? $default;
    }
}

if (!function_exists('settingCentral')) {
    function settingCentral($key, $default = null)
    {
        static $settingsCache = [];
        
        if (array_key_exists($key, $settingsCache)) {
            return $settingsCache[$key] ?? $default;
        }

        $setting = \App\Models\Setting::where('key', $key)->first();
        $settingsCache[$key] = $setting ? $setting->value : null;

        return $settingsCache[$key] ?? $default;
    }
}
