<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ChecklistTemplate extends Model
{
    protected $table = 'checklist_templates';

    protected $fillable = [
        'device_type_id',
        'name',
        'description',
        'is_active',
    ];

    public function deviceType()
    {
        return $this->belongsTo(DeviceType::class);
    }
}
