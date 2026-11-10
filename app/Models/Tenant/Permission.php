<?php

namespace App\Models\Tenant;

use Spatie\Permission\Models\Permission as ModelsPermission;

class Permission extends ModelsPermission
{
    protected $guard_name = 'tenant';
    // protected $connection = 'tenant';
    protected $fillable = ['name', 'guard_name'];
}
