<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @method static void deleted(\Closure|string|array $callback)
 * @method static void updated(\Closure|string|array $callback)
 */
trait HasDeletableFiles
{
    /**
     * Boot the trait to hook into model events.
     */
    public static function bootHasDeletableFiles()
    {
        // When a record is deleted, delete its files
        static::deleted(function ($model) {
            foreach ($model->getDeletableFileColumns() as $column) {
                if ($model->{$column}) {
                    Storage::disk($model->getDeletableFileDisk())->delete($model->{$column});
                }
            }
        });

        // When a record is updated, check if the file changed and delete the old one
        static::updated(function ($model) {
            foreach ($model->getDeletableFileColumns() as $column) {
                if ($model->isDirty($column)) {
                    $oldPath = $model->getOriginal($column);
                    if ($oldPath) {
                        Storage::disk($model->getDeletableFileDisk())->delete($oldPath);
                    }
                }
            }
        });
    }

    /**
     * Get the column names that store file paths.
     */
    public function getDeletableFileColumns(): array
    {
        return property_exists($this, 'deletableFileColumns') 
            ? $this->deletableFileColumns 
            : [];
    }

    /**
     * Get the directories to be scanned by the Garbage Collector.
     */
    public function getDeletableFileDirectories(): array
    {
        return property_exists($this, 'deletableFileDirectories')
            ? $this->deletableFileDirectories
            : [];
    }
    
    /**
     * Get the storage disk to use. Defaults to 'public'.
     */
    public function getDeletableFileDisk(): string
    {
        return property_exists($this, 'deletableFileDisk')
            ? $this->deletableFileDisk
            : 'public';
    }
}
