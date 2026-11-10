<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class FinanceAccountBalance extends Model
{
    protected $guarded = ['id'];

    public function account()
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }
}
