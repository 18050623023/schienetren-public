<?php

namespace App\Console\Commands;

use ReflectionClass;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Facades\Tenancy;
use Stancl\Tenancy\Database\Models\Tenant;
use Filament\Facades\Filament;

use Illuminate\Support\Facades\File;


use function Laravel\Prompts\select;

class SyncPermissionsToTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:permissions';



    protected ?string $generatorOption = null;
    protected ?string $guard = 'tenant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use Tenant:run to Synchronize permissions on all tenants. Customize by HaRa.';

    /**
     * Execute the console command.
     */
    public function handle()
    {


        // Ambil konfigurasi daftar aksi yang akan di pakai untuk membuat permission dan policy
        if (blank($this->generatorOption) ) {
            $this->generatorOption = Select(
                label: 'What do you want to generate?',
                options: [
                    'policies_and_permissions' => 'Policies & Permissions',
                    'policies' => 'Policies only',
                    'permissions' => 'Permissions only',
                ],
                default: 'Policies & Permissions',
            );
        }

        // Ambil Panel
        $panel = Filament::getPanel("tenant");

        // Ambil semua resources
        $resources = $panel->getResources();


        if (empty($resources)) {
            $this->error("No Resources found ");
            return;
        }

        $this->info('Starting Filament Policy or Permission Generation...');

        foreach ($resources as $resource) {
            // Ekstrak Resource Class
            $resourceClass = $this->extractResourceClass($resource);

            // Jika ekstraksi gagal, lanjutkan ke berikutnya
            if (!$resourceClass) continue;


            // if (! method_exists($resource, 'getModel')) {
            //     continue;
            // }
            // $model = $resource::getModel();

            $model = null;

            if (method_exists($resource, 'getModel')) {
                $model = $resource::getModel();
            } else {
                try {
                    $reflection = new ReflectionClass($resource);
                    $model = $reflection->getStaticPropertyValue('model');
                } catch (\ReflectionException $e) {
                    continue;
                }
            }

            // Jika ekstraksi gagal, lanjutkan ke berikutnya
            // if (!$model) continue;

            // dd($model);
            $this->proses($model);

        }

        $this->info('Permissions synced for tenant DB: ' . config('database.connections.mysql.database'));
    }

    protected function extractResourceClass(string $path): ?string
    {
        // Konversi path file menjadi Fully Qualified Class Name (FQCN)
        $relativeClass = str_replace([app_path(), '.php', '/'], ['', '', '\\'], $path);
        return "{$relativeClass}";
    }

    protected function proses(string $model): void
    {
        // Ekstrak Nama Model
        $modelName = class_basename($model); // Post
        $service = app(\App\Services\PermissionService::class);

         if ($this->generatorOption === 'permissions' || $this->generatorOption === 'policies_and_permissions') {

            // $this->line("DEBUG Permissions: <comment>{$modelName}</comment>");
            $service->syncPermissions($this, $modelName, Permission::class, Role::class, 'owner', 'tenant');

        }

        if ($this->generatorOption === 'policies' || $this->generatorOption === 'policies_and_permissions') {

            // $this->line("DEBUG Policies: <comment>{$this->generatorOption}</comment>");

            $service->generatePolicyFile($this, $model, $modelName, 'App\Policies\Tenant', 'App\Models\Tenant\User', config('custom.policies.tenant_path'));
        }
    }

}
