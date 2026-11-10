<?php

namespace App\Models\Tenant;

use App\Enums\TenantFeature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


class StockOpname extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'branch_id',
        'reference',
        'date',
        'notes',
        'type',
        'status',
        'created_by',
        'confirmed_by',
        'posted_by',
        'confirmed_at',
        'posted_at',

    ];


    protected function casts(): array
    {
        return [
            'date' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if ($model->type === 'regular' && function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME)) {
                throw new \Exception('Fitur Stock Opname (Audit) tidak aktif pada paket langganan Anda.');
            }

            if (empty($model->created_by) && Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            // dd($model);
            if (! ($model->status === 'draft' && $model->type === 'initial')) {
                throw new \Exception('Stock Opname tidak boleh dihapus.');;
            }
        });
    }

    public function serials()
    {
        return $this->hasMany(StockOpnameItemSerial::class);
    }

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cenceledBy()
    {
        return $this->belongsTo(User::class, 'cenceled_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'source');
    }

    /**
     * Query Scopes for Reporting & Filtering
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        if ($from && $to) {
            return $query->whereBetween('date', [
                \Carbon\Carbon::parse($from)->startOfDay(),
                \Carbon\Carbon::parse($to)->endOfDay(),
            ]);
        }
        if ($from) {
            return $query->where('date', '>=', \Carbon\Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            return $query->where('date', '<=', \Carbon\Carbon::parse($to)->endOfDay());
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

    public function scopeStatus($query, $status)
    {
        if (!$status) {
            return $query;
        }
        return $query->where('status', $status);
    }
}
