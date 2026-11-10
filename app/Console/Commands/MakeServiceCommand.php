<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name}';

    protected $description = 'Create a new service class';

    public function handle()
    {
        $name = $this->argument('name');

        $path = app_path("Services/{$name}.php");

        if (File::exists($path)) {
            $this->error('Service already exists!');

            return;
        }

        File::ensureDirectoryExists(app_path('Services'));

        $stub = <<<PHP
                    <?php

                    namespace App\Services;

                    class {$name}
                    {
                        //
                    }
                    PHP;

        File::put($path, $stub);

        $this->info("Service created: {$path}");
    }
}
