<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'type',
        'is_active',
    ];
}
