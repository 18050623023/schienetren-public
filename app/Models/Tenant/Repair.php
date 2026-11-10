<?php

namespace App\Models\Tenant;

use App\Enums\RepairStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Repair extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'code',
        'sequence',
        'branch_id',
        'client_id',
        'notes',
        'status',
        'delivered_at',
        'canceled_at',
        'approved_by',
        'created_by',
        'received_by',
        'canceled_by',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (Repair $repair) {
            $check = static::checkMonthlyRepairLimit();
            if (!$check->passed) {
                throw new \Exception($check->message);
            }
        });

        static::updated(function (Repair $repair) {
            // if ($repair->isDirty('status')) {
            if ($repair->wasChanged('status') && $repair->status === RepairStatus::Delivered) {
                self::generateRepairCommissions($repair);
                self::generateRepairWarranties($repair);
                self::completeWarrantyClaim($repair);
            }
        });
    }

    protected static function generateRepairCommissions(Repair $repair): void
    {
        $repair->load([
            'repairDevices.repairItems.productVariant.product',
            'repairDevices.technicians.employeeAssignment.compensationPackages' => function ($query) {
                $query->where('compensation_assignments.start_date', '<=', today())
                      ->where(function ($q) {
                          $q->whereNull('compensation_assignments.end_date')
                            ->orWhere('compensation_assignments.end_date', '>=', today());
                      })->with(['packageComponents' => function ($q) {
                          $q->whereIn('based_on', [
                              \App\Enums\CompensationPackageComponentBasedOn::RepairItem,
                              \App\Enums\CompensationPackageComponentBasedOn::RepairService,
                          ]);
                      }]);
            }
        ]);
        

        foreach ($repair->repairDevices as $device) {
            if ($device->technicians->isEmpty()) {
                continue;
            }

            foreach ($device->technicians as $technician) {
                $assignment = $technician->employeeAssignment;
                if (!$assignment || $assignment->compensationPackages->isEmpty()) {
                    continue;
                }

                foreach ($assignment->compensationPackages as $package) {
                    foreach ($package->packageComponents as $component) {
                        if (in_array($component->based_on, [\App\Enums\CompensationPackageComponentBasedOn::RepairItem, \App\Enums\CompensationPackageComponentBasedOn::RepairService])) {
                            foreach ($device->repairItems as $item) {
                                $productType = $item->productVariant?->product?->type ?? 'product';

                                if ($component->based_on === \App\Enums\CompensationPackageComponentBasedOn::RepairItem && $productType === 'service') {
                                    continue;
                                }
                                if ($component->based_on === \App\Enums\CompensationPackageComponentBasedOn::RepairService && $productType !== 'service') {
                                    continue;
                                }

                                $commissionNominal = 0;

                                if ($component->calc_type === 'percentage') {
                                    $commissionNominal = ($component->value / 100) * $item->subtotal_item;
                                } elseif ($component->calc_type === 'amount_per_unit') {
                                    $commissionNominal = $component->value * ($item->qty ?? 1);
                                } elseif ($component->calc_type === 'fixed') {
                                    $commissionNominal = $component->value;
                                } 
                                // Log::info('Package Components:', [
                                //         'Total komisi' => $commissionNominal
                                //     ]);

                                // UNCOMMENT JIKA TIPE KOMISI FORMULA SUDAH IMPLEMENTASI 
                                // elseif ($component->calc_type === 'formula') {
                                //     // ponytail: simplifikasi evaluasi formula. 
                                //     // Saat ini formula belum dievaluasi secara dinamis.
                                //     $commissionNominal = 0;
                                // }

                                if ($commissionNominal > 0) {
                                    \App\Models\Tenant\RepairTechnicianCommission::updateOrCreate(
                                        [
                                            'repair_technician_id' => $technician->id,
                                            'repair_item_id' => $item->id,
                                            'compensation_component_id' => $component->compensation_component_id,
                                        ],
                                        [
                                            'amount' => $commissionNominal,
                                            'notes' => 'Auto generated from ' . ($productType === 'service' ? 'Repair Service' : 'Repair Item') . ': ' . ($item->productVariant?->name ?? 'Item'),
                                        ]
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    protected static function generateRepairWarranties(Repair $repair): void
    {
        $repair->loadMissing(['repairDevices.repairItems.productVariant.product', 'warrantyClaim.repairItemWarranty']);
        
        $isWarrantyClaim = $repair->warrantyClaim !== null;
        $originalWarrantyEndDate = $isWarrantyClaim ? $repair->warrantyClaim->repairItemWarranty?->end_date : null;

        foreach ($repair->repairDevices as $device) {
            foreach ($device->repairItems as $item) {
                $product = $item->productVariant?->product;
                
                if ($product && $product->warranty_value > 0) {
                    $startDate = $repair->delivered_at ? \Carbon\Carbon::parse($repair->delivered_at) : now();
                    
                    if ($isWarrantyClaim && $originalWarrantyEndDate) {
                        // Ponytail: Poin 3 - Carry-over masa garansi. Teruskan sisa waktu dari garansi awal.
                        $endDate = \Carbon\Carbon::parse($originalWarrantyEndDate);
                        if ($endDate->isPast()) {
                            // Jika garansi awal sudah habis saat perbaikan RMA selesai, tidak ada garansi lanjutan.
                            continue;
                        }
                    } else {
                        $endDate = match($product->warranty_unit) {
                            'day' => $startDate->copy()->addDays($product->warranty_value),
                            'month' => $startDate->copy()->addMonths($product->warranty_value),
                            'year' => $startDate->copy()->addYears($product->warranty_value),
                            default => null
                        };
                    }

                    if ($endDate) {
                        \App\Models\Tenant\RepairItemWarranty::updateOrCreate(
                            [
                                'repair_item_id' => $item->id,
                            ],
                            [
                                'duration_value' => $product->warranty_value,
                                'duration_unit' => $product->warranty_unit,
                                'start_date' => $startDate,
                                'end_date' => $endDate,
                                'status' => 'active',
                            ]
                        );
                    }
                }
            }
        }
    }

    protected static function completeWarrantyClaim(Repair $repair): void
    {
        if ($repair->warrantyClaim && $repair->warrantyClaim->status !== 'completed') {
            $solution = $repair->warrantyClaim->solution;
            if (empty($solution)) {
                $solution = 'Perbaikan garansi telah diselesaikan pada tiket repair: ' . $repair->code;
            }

            $repair->warrantyClaim->update([
                'status' => 'completed',
                'solution' => $solution
            ]);
        }
    }

    protected function casts(): array
    {
        return [
            'status' => RepairStatus::class,
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function repairDevices()
    {
        return $this->hasMany(RepairDevice::class);
    }

    // public function repairItems()
    // {
    //     return $this->hasManyThrough(RepairItem::class, RepairDevice::class);
    // }

    public function repairHandover()
    {
        return $this->hasOne(RepairHandover::class);
    }
    public function repairInvoice()
    {
        return $this->hasOne(RepairInvoice::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function canceledBy()
    {
        return $this->belongsTo(User::class, 'canceled_by');
    }


    public function warrantyClaim()
    {
        return $this->hasOne(RepairWarrantyClaim::class, 'new_repair_id');
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
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

    public function scopeForClient($query, $clientId)
    {
        if (!$clientId) {
            return $query;
        }
        return $query->where('client_id', $clientId);
    }

    public function scopeForTechnician($query, $assignmentId)
    {
        if (!$assignmentId) {
            return $query;
        }
        return $query->whereHas('repairDevices.technicians', function ($q) use ($assignmentId) {
            $q->where('employee_assignment_id', $assignmentId);
        });
    }

    public static function checkMonthlyRepairLimit(?self $record = null): \App\DTOs\FeatureCheckResult
    {
        $tenant = tenant();
        if (!$tenant) {
            return \App\DTOs\FeatureCheckResult::passed();
        }

        // Cek status toggle on/off dari fitur
        $toggle = \App\Models\Tenant\FeatureToggle::where('feature_code', \App\Enums\TenantFeature::MAX_MONTHLY_REPAIR->value)->first();
        if ($toggle && !$toggle->is_enabled) {
            return \App\DTOs\FeatureCheckResult::disabled('Fitur kuota tiket servis bulanan sedang dinonaktifkan.');
        }

        $maxLimit = $tenant->getFeatureValue(\App\Enums\TenantFeature::MAX_MONTHLY_REPAIR);
        if ($maxLimit === null || $maxLimit === false || $maxLimit < 0) {
            return \App\DTOs\FeatureCheckResult::passed(); // Unlimited
        }

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $currentCount = static::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        if ($record && !empty($record->exists)) {
            if ($record->created_at && $record->created_at->between($startOfMonth, $endOfMonth)) {
                $currentCount--;
            }
        }

        if ($currentCount >= $maxLimit) {
            return \App\DTOs\FeatureCheckResult::limitExceeded("Batas kuota servis bulanan ({$maxLimit} tiket) telah tercapai untuk bulan " . now()->translatedFormat('F Y') . ".");
        }

        return \App\DTOs\FeatureCheckResult::passed();
    }
}
