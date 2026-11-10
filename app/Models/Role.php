<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as ModelsRole;

class Role extends ModelsRole
{
    use SoftDeletes ;

    protected $guard_name = 'web';

    protected $fillable = ['name', 'guard_name'];

    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function ($builder) {
            $builder->whereNull('roles.deleted_at');
        });
    }
}
