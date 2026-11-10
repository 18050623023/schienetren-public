<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'company',
        'whatsapp',
        'description',
        'address',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    public function purcahases()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
