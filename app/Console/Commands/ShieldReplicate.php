<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Filament\Facades\Filament;
use ReflectionClass;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

class ShieldReplicate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:shield-replicate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Spatie Permissions and Laravel Policies for Filament Resources (ONLY ADMIN PANEL). Customize by HaRa.';



    protected ?string $generatorOption = null;
    protected ?string $guard = null;


    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ambil Panel
        $panel = Filament::getPanel("admin");

        // Ambil semua resources
        $resources = $panel->getResources();

        // Ambil Guard
        $this->guard = $panel->getAuthGuard();


        if (empty($resources)) {
            $this->error("No Resources found ");
            return;
        }


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

        // Loop melalui setiap Resource
        foreach ($resources as $file) {

            // Ekstrak Resource Class
            $resourceClass = $this->extractResourceClass($file);

            // Jika ekstraksi gagal, lanjutkan ke berikutnya
            if (!$resourceClass) continue;

            // Ekstrak Model
            $model = $this->extractModelFromResource($resourceClass);

            // Jika ekstraksi berhasil, proses model
            if ($model) {

                $this->processModel($model);

            }
        }

        $this->info('Generation Complete!');

        return Command::SUCCESS;
    }



    protected function extractResourceClass(string $path): ?string
    {
        // Konversi path file menjadi Fully Qualified Class Name (FQCN)
        $relativeClass = str_replace([app_path(), '.php', '/'], ['', '', '\\'], $path);
        return "{$relativeClass}";
    }

    protected function extractModelFromResource(string $resourceClass): ?string
    {
        try {
            // Menggunakan Reflection untuk mendapatkan nilai properti $model
            $reflection = new ReflectionClass($resourceClass);
            $model = $reflection->getStaticPropertyValue('model');
            return $model;

        } catch (\Throwable $th) {
            $this->error("Could not extract model from {$resourceClass}: " . $th->getMessage());
            return null;
        }
    }

    protected function processModel(string $model): void
    {
        $modelName = class_basename($model); // Post
        $actions = config('custom.policies.methods');//['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];
        $permissionSlug = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName)); // post



        $this->info('Starting Filament Policy and Permission Generation...');
        $service = app(\App\Services\PermissionService::class);

        if ($this->generatorOption === 'permissions' || $this->generatorOption === 'policies_and_permissions') {

            $this->line("DEBUG Permissions: <comment>{$modelName}</comment>");
            $service->syncPermissions($this, $modelName, Permission::class, Role::class, 'super_admin', $this->guard);

        }

        if ($this->generatorOption === 'policies' || $this->generatorOption === 'policies_and_permissions') {

            $service->generatePolicyFile($this, $model, $modelName, 'App\Policies', 'App\Models\User', app_path('Policies'));

        }


        $this->info("Synchronization complete.");

    }


    // Generate Permissions
    protected function generatePermissions($actions, $permissionSlug) {

        foreach ($actions as $action) {

            $permissionName = "{$action}:{$permissionSlug}";

            if (!tenant_model('Permission')::where('name', $permissionName)->exists()) {
                tenant_model('Permission')::create(['name' => $permissionName, 'guard_name' => $this->guard]);

                $role = tenant_model('Role')::findByName('super_admin', $this->guard);
                $role->givePermissionTo($permissionName);

                $this->line("   - Created Permission: {$permissionName}");

            }else {

                $this->warn("   - Permission {$permissionName} already exists. Skipping creation.");

            }
        }
    }

    // Generate Policy File
    protected function generatePolicyFile($model, $modelName, $actions) {

        $modelVar = strtolower($modelName); // post

        $policyName = "{$modelName}Policy"; // PostPolicy

        $policyPath = config('custom.policies.path')."\\$policyName.php";

        // Ambil string lokasi stub
        $stubPath = base_path('stubs/filament-policy.stub');
        $multiParam = base_path('stubs/MultiParamMethod.stub');
        $singleParam = base_path('stubs/SingleParamMethod.stub');

        // Initialize $methods
        $methods = '';

        foreach ($actions as $action) {


            // Tentukan apakah aksi memerlukan instance model (seperti view, update, delete)
            $needsModelInstance = in_array($action, config('custom.policies.single_parameter_methods'));

            // Tentukan parameter fungsi
            if ($needsModelInstance) {

                $stubSingle = File::get($singleParam);

                $methods .= str_replace(
                    [
                        '{{ methodName }}',
                        '{{ permission }}',
                    ],
                    [
                        $action,
                        $action . ':' . $modelVar,
                    ],
                    $stubSingle
                );

            } else {

                $stubMulti = File::get($multiParam);

                $methods .= str_replace(
                    [
                        '{{ methodName }}',
                        '{{ permission }}',
                        '{{ model_name }}',
                        '{{ model_variable }}'
                    ],
                    [
                        $action,
                        $action . ':' . $modelVar,
                        $modelName,
                        $modelVar,
                    ],
                    $stubMulti
                );
            }
        }

        $stub = File::get($stubPath);

        // Lakukan penggantian placeholder
        $content = str_replace(
            [
                '{{ NAMESPACE }}',
                '{{ MODEL_USER }}',
                '{{ MODEL_NAMESPACE }}',
                '{{ MODEL_NAME }}',
                '{{ POLICY_METHODS }}'
            ],
            [
                'App\Policies' ,
                'App\Models\User' ,
                $model,
                $modelName,
                $methods,
            ],
            $stub
        );

        // Pastikan direktori Policies ada
        if (!File::isDirectory(app_path('Policies'))) {
            File::makeDirectory(app_path('Policies'), 0755, true);
        }

        if (!File::exists($policyPath)) {
            $this->line("   - Created Permission: {$policyPath}");

            File::put($policyPath, $content);

            return true;
        }
        else {
            $this->warn("   - Policy {$policyName} already exists. Skipping file creation.");
            return false;
        }
        $this->info("   - Policy {$policyName} created successfully at: {$policyPath}");
    }
}
