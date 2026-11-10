<?php

namespace App\Enums;

enum CompensationPackageComponentBasedOn : string
{
    case Fixed = 'fixed';
    case BasicSalary = 'basic_salary';
    case RepairService = 'repair_service';
    case RepairItem = 'repair_item';
    case Sales = 'sales';
    case Attendance = 'attendance';
    case Overtime = 'overtime_hour';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed',
            self::BasicSalary => 'Basic Salary',
            self::RepairService => 'Repair Service',
            self::RepairItem => 'Repair Item',
            self::Sales => 'Sales',
            self::Attendance => 'Attendance',
            self::Overtime => 'Overtime',
            self::Custom => 'Custom',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }
}
