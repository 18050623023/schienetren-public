<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RepairStatus: string implements HasLabel, HasColor
{
    case Incoming = 'incoming';
    case Checking = 'checking';
    case WaitingConfirmation = 'waiting_confirmation';
    case WaitingSparepart = 'waiting_sparepart';
    case SparepartArrived = 'sparepart_arrived';
    case InService = 'in_service';
    case RunningTest = 'running_test';
    case PartialyDone = 'partially_done';
    case ReadyToPickup = 'ready_to_pickup';
    case Delivered = 'delivered';
    case TransferTechnician = 'transfer_technician';
    case NotPossible = 'not_possible';
    case Cancel = 'cancel';

    public function label(): string
    {
        return match ($this) {
            self::Incoming => 'Servis Masuk',
            self::Checking => 'Pemeriksaan / Diagnosa',
            self::WaitingConfirmation => 'Menunggu Konfirmasi/Acc',
            self::WaitingSparepart => 'Menunggu Sparepart',
            self::SparepartArrived => 'Sparepart Datang',
            self::InService => 'Proses Servis',
            self::RunningTest => 'Running Test / QC',
            self::PartialyDone => 'Selesai Sebagian',
            self::ReadyToPickup => 'Bisa Diambil',
            self::Delivered => 'Sudah Diambil',
            self::TransferTechnician => 'Oper Teknisi Lain',
            self::NotPossible => 'Tidak Bisa',
            self::Cancel => 'Cancel',
        };
    }

    public function colors(): string
    {
        return match ($this) {
            self::Incoming => 'primary',
            self::Checking => 'info',
            self::WaitingConfirmation => 'warning',
            self::WaitingSparepart => 'warning',
            self::SparepartArrived => 'warning',
            self::InService => 'warning',
            self::RunningTest => 'warning',
            self::PartialyDone => 'warning',
            self::ReadyToPickup => 'success',
            self::Delivered => 'success',
            self::TransferTechnician => 'warning',
            self::NotPossible => 'danger',
            self::Cancel => 'danger',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getColor(): string | array | null
    {
        return $this->colors();
    }
}
