<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as ModelsRole;
use Spatie\Permission\Traits\HasPermissions;

class Role extends ModelsRole
{
    use SoftDeletes;//, HasPermissions;

    protected $guard_name = 'tenant';
    // protected $connection = 'tenant';

    protected $fillable = ['name', 'guard_name'];

    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function ($builder) {
            $builder->whereNull('roles.deleted_at');
        });
    }
}
