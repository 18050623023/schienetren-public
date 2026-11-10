<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceType extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'name',
    ];

    public function checklists()
    {
        return $this->hasMany(ChecklistTemplate::class);
    }

    public function deviceModels()
    {
        return $this->hasMany(DeviceModel::class);
    }
}
