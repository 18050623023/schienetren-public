<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class RepairPhoto extends Model
{
    use \App\Traits\HasDeletableFiles;

    protected $fillable = [
        'repair_device_id',
        'type',
        'path',
        'taken_by',
    ];

    protected $casts = [
        'path' => 'array',
    ];
    public array $deletableFileColumns = ['path'];
    public array $deletableFileDirectories = ['images/repair_photos'];
}
