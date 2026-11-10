<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RepairDeviceStatus: string implements HasLabel, HasColor, HasIcon
{
    case Todo = 'todo';
    case Checking = 'checking';
    case WaitingConfirmation = 'waiting_confirmation';
    case InProgress = 'in_progress';
    case Escalated = 'escalated';
    case WaitingParts = 'waiting_parts';
    case QC = 'qc';
    case ReadyPickup = 'ready_pickup';
    case Done = 'done';
    case NotPossible = 'not_possible';
    case Cancel = 'cancel';


    public function label(): string
    {
        return match ($this) {
            self::Todo => 'Todo',
            self::Checking => 'Checking',
            self::WaitingConfirmation => 'Waiting Confirmation',
            self::InProgress => 'In Progress',
            self::Escalated => 'Escalated',
            self::WaitingParts => 'Waiting Parts',
            self::QC => 'QC',
            self::ReadyPickup => 'Ready Pickup',
            self::Done => 'Done',
            self::NotPossible => 'Not Possible',
            self::Cancel => 'Cancel',
        };
    }

    public function colors(): string
    {
        return match ($this) {
            self::Todo => 'gray',
            self::Checking => 'info',
            self::WaitingConfirmation => 'warning',
            self::InProgress => 'warning',
            self::Escalated => 'warning',
            self::WaitingParts => 'warning',
            self::QC => 'warning',
            self::ReadyPickup => 'success',
            self::Done => 'success',
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

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Todo => 'heroicon-m-clipboard-document-list',
            self::Checking => 'heroicon-m-magnifying-glass',
            self::WaitingConfirmation => 'heroicon-m-clock',
            self::InProgress => 'heroicon-m-wrench-screwdriver',
            self::Escalated => 'heroicon-m-arrow-trending-up',
            self::WaitingParts => 'heroicon-m-cube',
            self::QC => 'heroicon-m-check-badge',
            self::ReadyPickup => 'heroicon-m-shopping-bag',
            self::Done => 'heroicon-m-check',
            self::NotPossible => 'heroicon-m-x-circle',
            self::Cancel => 'heroicon-m-archive-box-x-mark',
        };
    }
}
