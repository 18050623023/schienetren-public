<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class FinanceJournalItem extends Model
{
    protected $guarded = ['id'];

    public function journal()
    {
        return $this->belongsTo(FinanceJournal::class, 'finance_journal_id');
    }

    public function account()
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Tenant\Branch::class);
    }
}
