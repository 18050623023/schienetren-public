<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceAccountCategory extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function accounts()
    {
        return $this->hasMany(FinanceAccount::class);
    }
}
