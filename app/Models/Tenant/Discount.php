<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'min_transaction',
        'max_discount',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function repairInvoices()
    {
        return $this->hasMany(RepairInvoice::class);
    }
}
