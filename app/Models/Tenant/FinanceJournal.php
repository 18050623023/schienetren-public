<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceJournal extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany(FinanceJournalItem::class);
    }

    public function journalable()
    {
        return $this->morphTo();
    }
    
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Tenant\Branch::class);
    }
}
