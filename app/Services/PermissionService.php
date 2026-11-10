<?php

namespace App\Services;

use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class PermissionService
{
    /**
     * Generate dynamic permission schema for Role forms.
     */
    public function getDynamicPermissionSchema(string $panelId, string $permissionModelClass): array
    {
        $panel = Filament::getPanel($panelId);
        $resources = $panel->getResources();
        $sections = [];

        foreach ($resources as $resource) {
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

            if (!$model) continue;

            $modelName = Str::snake(class_basename($model));

            $permissions = $permissionModelClass::query()
                ->where('name', 'like', "%:$modelName")
                ->pluck('name', 'name')
                ->toArray();

            if (empty($permissions)) {
                continue;
            }

            $sections[] = Section::make(class_basename($resource))
                ->schema([
                    CheckboxList::make("permissions.$modelName")
                        ->options($permissions)
                        ->columns(2)
                        ->bulkToggleable(),
                ])
                ->collapsible()
                ->collapsed();
        }

        return $sections;
    }

    /**
     * Sync permissions to database and assign to role.
     */
    public function syncPermissions(Command $command, string $modelName, string $permissionModelClass, string $roleModelClass, string $roleName, string $guardName): void
    {
        $permissionSlug = Str::snake(class_basename($modelName));
        $actions = config('custom.policies.methods', ['viewAny', 'view', 'create', 'update', 'delete']);

        foreach ($actions as $action) {
            $permissionName = "{$action}:{$permissionSlug}";

            if (!$permissionModelClass::where('name', $permissionName)->exists()) {
                $permissionModelClass::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => $guardName,
                ]);
                $command->line("   - Created Permission: {$permissionName}");
            } else {
                $command->warn("   - Permission {$permissionName} already exists.");
            }

            // Ensure the role gets the permission even if the permission already existed.
            if (class_exists($roleModelClass)) {
                $role = $roleModelClass::findByName($roleName, $guardName);
                if ($role) {
                    if (!$role->hasPermissionTo($permissionName)) {
                        $role->givePermissionTo($permissionName);
                        $command->info("   - Assigned permission {$permissionName} to {$roleName}.");
                    }
                }
            }
        }
    }

    /**
     * Generate Policy File.
     */
    public function generatePolicyFile(Command $command, string $model, string $modelName, string $namespace, string $userModel, string $directory): bool
    {
        $actions = config('custom.policies.methods', ['viewAny', 'view', 'create', 'update', 'delete']);
        $modelVar = Str::snake(class_basename($modelName));
        $policyName = "{$modelName}Policy";
        $policyPath = rtrim($directory, '/') . "/{$policyName}.php";

        $stubPath = base_path('stubs/filament-policy.stub');
        $multiParam = base_path('stubs/MultiParamMethod.stub');
        $singleParam = base_path('stubs/SingleParamMethod.stub');

        $methods = '';
        $singleParamMethods = config('custom.policies.single_parameter_methods', ['view', 'update', 'delete', 'restore', 'forceDelete']);

        foreach ($actions as $action) {
            $needsModelInstance = in_array($action, $singleParamMethods);

            if ($needsModelInstance) {
                $stubSingle = File::get($singleParam);
                $methods .= str_replace(
                    ['{{ methodName }}', '{{ permission }}'],
                    [$action, $action . ':' . $modelVar],
                    $stubSingle
                );
            } else {
                $stubMulti = File::get($multiParam);
                $methods .= str_replace(
                    ['{{ methodName }}', '{{ permission }}', '{{ model_name }}', '{{ model_variable }}'],
                    [$action, $action . ':' . $modelVar, $modelName, $modelVar],
                    $stubMulti
                );
            }
        }

        $stub = File::get($stubPath);

        $content = str_replace(
            ['{{ NAMESPACE }}', '{{ MODEL_USER }}', '{{ MODEL_NAMESPACE }}', '{{ MODEL_NAME }}', '{{ POLICY_METHODS }}'],
            [$namespace, $userModel, $model, $modelName, $methods],
            $stub
        );

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (!File::exists($policyPath)) {
            File::put($policyPath, $content);
            $command->info("   - Created Policy {$policyName} at: {$policyPath}");
            return true;
        } else {
            $command->warn("   - Policy {$policyName} already exists. Skipping file creation.");
            return false;
        }
    }
}
