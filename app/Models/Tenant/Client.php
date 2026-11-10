<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'whatsapp',
        'address',
    ];

    public function repair()
    {
        return $this->hasMany(Repair::class);
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    public function scopeForBranch($query, $branchId)
    {
        if (!$branchId) {
            return $query;
        }
        return $query->whereHas('repair', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });
    }

    public function scopeHasRepairs($query)
    {
        return $query->has('repair');
    }
}
