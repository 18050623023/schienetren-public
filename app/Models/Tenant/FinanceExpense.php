<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceExpense extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    protected static function booted()
    {
        static::observe(\App\Observers\Tenant\FinanceExpenseObserver::class);
    }

    public function account()
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(\App\Models\Tenant\PaymentMethod::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Tenant\Branch::class);
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('date', '<=', $to);
        }
        return $query;
    }

    public function scopeForBranch($query, $branchId)
    {
        if (!$branchId) {
            return $query;
        }
        return $query->where('branch_id', $branchId);
    }

    public function scopeForAccount($query, $accountId)
    {
        if (!$accountId) {
            return $query;
        }
        return $query->where('finance_account_id', $accountId);
    }

    public function scopeForPaymentMethod($query, $methodId)
    {
        if (!$methodId) {
            return $query;
        }
        return $query->where('payment_method_id', $methodId);
    }
}
