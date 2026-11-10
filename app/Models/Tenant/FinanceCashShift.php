<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class FinanceCashShift extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Tenant\Branch::class);
    }
}
