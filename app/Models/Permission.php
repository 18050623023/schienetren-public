<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as ModelsPermission;

class Permission extends ModelsPermission
{

    protected $guard_name = 'web';

    protected $fillable = ['name', 'guard_name'];



}
