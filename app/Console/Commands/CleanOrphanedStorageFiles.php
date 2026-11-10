<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;

class CleanOrphanedStorageFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:clean-orphans {--dry-run : Only show files to be deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean orphaned files from storage by checking models using HasDeletableFiles trait';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Garbage Collection...');
        $dryRun = $this->option('dry-run');
        
        $models = $this->getModelsWithHasDeletableFilesTrait();
        
        if (empty($models)) {
            $this->info('No models found using HasDeletableFiles trait.');
            return;
        }

        foreach ($models as $modelClass) {
            $this->info("Processing Model: {$modelClass}");
            $model = new $modelClass();
            
            $directories = $model->getDeletableFileDirectories();
            $columns = $model->getDeletableFileColumns();
            $disk = $model->getDeletableFileDisk();

            if (empty($directories) || empty($columns)) {
                $this->warn("Skipping {$modelClass} due to empty directories or columns.");
                continue;
            }

            // Get all valid paths from database
            $validPaths = [];
            $modelClass::query()->chunk(500, function ($records) use (&$validPaths, $columns) {
                foreach ($records as $record) {
                    foreach ($columns as $col) {
                        if ($record->{$col}) {
                            $validPaths[] = $record->{$col};
                        }
                    }
                }
            });

            // Iterate over each directory
            foreach ($directories as $directory) {
                if (!Storage::disk($disk)->exists($directory)) {
                    $this->warn("Directory {$directory} does not exist on disk {$disk}.");
                    continue;
                }

                $allFiles = Storage::disk($disk)->allFiles($directory);
                
                $orphanedCount = 0;
                foreach ($allFiles as $file) {
                    if (!in_array($file, $validPaths)) {
                        $orphanedCount++;
                        if ($dryRun) {
                            $this->warn("[DRY-RUN] Would delete: {$file}");
                        } else {
                            Storage::disk($disk)->delete($file);
                            $this->line("Deleted: {$file}");
                        }
                    }
                }
                
                $this->info("Found {$orphanedCount} orphaned files in {$directory}.");
            }
        }
        
        $this->info('Garbage Collection completed!');
    }

    /**
     * Get all models that use the HasDeletableFiles trait.
     */
    protected function getModelsWithHasDeletableFilesTrait(): array
    {
        $modelsPath = app_path('Models');
        $models = [];
        
        $finder = new Finder();
        $finder->files()->name('*.php')->in($modelsPath);
        
        foreach ($finder as $file) {
            $class = 'App\\Models\\' . str_replace('/', '\\', $file->getRelativePathname());
            $class = str_replace('.php', '', $class);
            
            if (class_exists($class)) {
                $traits = class_uses_recursive($class);
                if (in_array(\App\Traits\HasDeletableFiles::class, $traits)) {
                    $models[] = $class;
                }
            }
        }
        
        return $models;
    }
}
