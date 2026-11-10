<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceAccount extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(FinanceAccountCategory::class, 'finance_account_category_id');
    }

    public function journalItems()
    {
        return $this->hasMany(FinanceJournalItem::class);
    }
}
