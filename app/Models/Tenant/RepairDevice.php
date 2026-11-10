<?php

namespace App\Models\Tenant;

use App\Enums\RepairDeviceStatus;
use Illuminate\Database\Eloquent\Model;

class RepairDevice extends Model
{
    protected $table = 'repair_devices';
    protected $fillable = [
        'id',
        'repair_id',
        'repair_handover_id',
        'device_type_id',
        'brand_id',
        'model',
        'serial_number',
        'imei',
        'color',
        'accessories',
        'checklist',
        'problems',
        'diagnosis',
        'notes',
        'status',
        'current_stage',
        'position',
        'estimated_finish',
        'finished_at',
    ];

    public function casts(): array
    {
        return [
            'status' => RepairDeviceStatus::class,
            'checklist' => 'array',
        ];
    }

    protected static function booted()
    {
        static::updated(function (RepairDevice $device) {
            if ($device->isDirty('status')) {

                // Auto-assign primary technician
                if (in_array($device->status, [\App\Enums\RepairDeviceStatus::Checking, \App\Enums\RepairDeviceStatus::InProgress])) {
                    if (!$device->primaryTechnician()->exists()) {
                        $user = \Illuminate\Support\Facades\Auth::user();
                        if ($user && $user->employees) {
                            $activeAssignment = $user->employees->assignments()
                                ->where(function ($q) {
                                    $q->whereNull('end_date')
                                      ->orWhereDate('end_date', '>=', now());
                                })
                                ->latest()
                                ->first();

                            if ($activeAssignment) {
                                $device->technicians()->create([
                                    'employee_assignment_id' => $activeAssignment->id,
                                    'role' => 'primary',
                                ]);
                            }
                        }
                    } else {
                        // Jika sudah ada primary, cek apakah ini tiket handover yang baru diambil dari Todo
                        $originalStatus = $device->getOriginal('status');
                        $originalStatusValue = $originalStatus instanceof \BackedEnum ? $originalStatus->value : $originalStatus;

                        if ($originalStatusValue === \App\Enums\RepairDeviceStatus::Todo->value && $device->current_stage === 'software') {
                            $user = \Illuminate\Support\Facades\Auth::user();
                            if ($user && $user->employees) {
                                $activeAssignment = $user->employees->assignments()
                                    ->where(function ($q) {
                                        $q->whereNull('end_date')
                                          ->orWhereDate('end_date', '>=', now());
                                    })
                                    ->latest()
                                    ->first();

                                if ($activeAssignment) {
                                    // Pastikan dia belum terdaftar sebagai teknisi di device ini
                                    if (!$device->technicians()->where('employee_assignment_id', $activeAssignment->id)->exists()) {
                                        $device->technicians()->create([
                                            'employee_assignment_id' => $activeAssignment->id,
                                            // Role baru: specialist
                                            'role' => 'specialist',
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                } elseif ($device->status === \App\Enums\RepairDeviceStatus::Todo) {
                    if ($device->current_stage === 'software') {
                        // Hapus HANYA teknisi spesialis (Joko/Teknisi Software yang batal), pertahankan primary (Budi/Teknisi Hardware)
                        $device->technicians()->where('role', 'specialist')->delete();
                    } else {
                        // Jika masih di tahap hardware dan dikembalikan ke Todo, hapus semuanya (batal total)
                        $device->technicians()->delete();
                    }
                }

                $repair = $device->repair;
                if (!$repair) return;

                // Ambil array of string dari Enum values
                $statuses = $repair->repairDevices()->pluck('status')->map(fn($s) => $s instanceof \BackedEnum ? $s->value : $s);

                // Cek berdasarkan string value
                $hasStatus = fn($statusEnum) => $statuses->contains($statusEnum->value);
                
                $hasChecking = $hasStatus(\App\Enums\RepairDeviceStatus::Checking);
                $hasWaitingConfirmation = $hasStatus(\App\Enums\RepairDeviceStatus::WaitingConfirmation);
                $hasReadyPickup = $hasStatus(\App\Enums\RepairDeviceStatus::ReadyPickup);
                $hasTodo = $hasStatus(\App\Enums\RepairDeviceStatus::Todo);
                
                // Kumpulkan value dari status in-progress (kecuali Todo)
                $inProgressStatuses = [
                    \App\Enums\RepairDeviceStatus::InProgress->value,
                    \App\Enums\RepairDeviceStatus::WaitingParts->value,
                    \App\Enums\RepairDeviceStatus::QC->value,
                ];
                $hasInProgress = $statuses->intersect($inProgressStatuses)->isNotEmpty();

                $newRepairStatus = null;

                if ($hasChecking) {
                    $newRepairStatus = \App\Enums\RepairStatus::Checking;
                } elseif ($hasWaitingConfirmation) {
                    $newRepairStatus = \App\Enums\RepairStatus::WaitingConfirmation;
                } elseif ($hasReadyPickup && ($hasInProgress || $hasTodo)) {
                    $newRepairStatus = \App\Enums\RepairStatus::PartialyDone;
                } elseif ($hasInProgress) {
                    $newRepairStatus = \App\Enums\RepairStatus::InService;
                } elseif ($hasTodo) {
                    $newRepairStatus = \App\Enums\RepairStatus::Incoming;
                } elseif ($hasReadyPickup) {
                    $newRepairStatus = \App\Enums\RepairStatus::ReadyToPickup;
                } else {
                    $hasCancel = $hasStatus(\App\Enums\RepairDeviceStatus::Cancel);
                    $hasNotPossible = $hasStatus(\App\Enums\RepairDeviceStatus::NotPossible);
                    
                    if ($hasNotPossible) {
                        $newRepairStatus = \App\Enums\RepairStatus::NotPossible;
                    } elseif ($hasCancel) {
                        $newRepairStatus = \App\Enums\RepairStatus::Cancel;
                    }
                }

                // Update jika status baru ditemukan dan berbeda dengan status saat ini.
                // Status 'Delivered' diabaikan karena merupakan aksi final di tingkat Repair.
                if ($newRepairStatus && 
                    $newRepairStatus !== $repair->status && 
                    $repair->status !== \App\Enums\RepairStatus::Delivered) {
                    
                    $repair->update(['status' => $newRepairStatus]);
                }
            }
        });
    }

    public function repair()
    {
        return $this->belongsTo(Repair::class);
    }

    public function deviceType()
    {
        return $this->belongsTo(DeviceType::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function getBrandModelAttribute()
    {
        return trim(($this->brand->name ?? '') . ' ' . $this->model);
    }

    public function repairItems()
    {
        return $this->hasMany(RepairItem::class);
    }

    public function repairPhotos()
    {
        return $this->hasMany(RepairPhoto::class);
    }

    public function primaryTechnician()
    {
        return $this->hasOne(RepairTechnician::class)->where('role', 'primary');
    }

    // Untuk mengambil semua teknisi (termasuk assistant dan qc)
    public function technicians()
    {
        return $this->hasMany(RepairTechnician::class);
    }

    public function repairHandover()
    {
        return $this->belongsTo(RepairHandover::class);
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
        return $query->whereHas('repair', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });
    }

    public function scopeForBrand($query, $brandId)
    {
        if (!$brandId) {
            return $query;
        }
        return $query->where('brand_id', $brandId);
    }

    public function scopeForDeviceType($query, $deviceTypeId)
    {
        if (!$deviceTypeId) {
            return $query;
        }
        return $query->where('device_type_id', $deviceTypeId);
    }

    public function scopeStatus($query, $status)
    {
        if (!$status) {
            return $query;
        }
        return $query->where('status', $status);
    }
}
