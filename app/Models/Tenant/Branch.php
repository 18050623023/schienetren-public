<?php

namespace App\Models\Tenant;

use EduardoRibeiroDev\FilamentLeaflet\ValueObjects\Coordinate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasTenantLimits;

class Branch extends Model
{
    use SoftDeletes;
    use HasTenantLimits;

    protected $fillable = [
        'name',
        'code',
        'phone',
        'address',
        'latitude',
        'longitude',
        'attendance_radius',
        'is_active',
    ];

    protected $casts = [
        'location' => Coordinate::class . ':latitude,longitude', // If the lat/lng columns don't follow the config default names, specify them here
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function stockOpnameItems()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockOpnames()
    {
        return $this->hasMany(StockOpname::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }

    public function employeeAssignments()
    {
        return $this->hasMany(EmployeeAssignment::class);
    }

    public function repair()
    {
        return $this->hasMany(Repair::class);
    }

    public function financeExpenses()
    {
        return $this->hasMany(FinanceExpense::class);
    }

    /**
     * Helper untuk Finance Simple: Hitung Pemasukan (Total Pembayaran Servis yang Lunas)
     */
    public function getSimpleTotalIncome(?string $startDate = null, ?string $endDate = null): float
    {
        $query = \App\Models\Tenant\RepairPayment::whereHas('repairInvoice.repair', function ($q) {
            $q->where('branch_id', $this->id);
        });

        if ($startDate && $endDate) {
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Helper untuk Finance Simple: Hitung Pengeluaran
     */
    public function getSimpleTotalExpense(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->financeExpenses();
        
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Helper untuk Finance Simple: Laba Bersih
     */
    public function getSimpleNetProfit(?string $startDate = null, ?string $endDate = null): float
    {
        return $this->getSimpleTotalIncome($startDate, $endDate) - $this->getSimpleTotalExpense($startDate, $endDate);
    }

    /**
     * Check if activating a branch exceeds the tenant's max_branch feature limit.
     * Returns a FeatureCheckResult.
     */
    public static function checkActiveLimit(?self $record = null): \App\DTOs\FeatureCheckResult
    {
        return self::checkFeatureLimit(
            \App\Enums\TenantFeature::MAX_BRANCH,
            'cabang aktif',
            'is_active',
            $record
        );
    }
}
