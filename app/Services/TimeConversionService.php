<?php

namespace App\Services;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;

class TimeConversionService
{
    /**
     * Konversi dari nilai dan satuan ke total hari
     * 
     * @param int $value
     * @param string $unit (hari, minggu, bulan, tahun)
     * @return int
     */
    public static function humanToDay($value, $unit)
    {
        $value = (int) $value;
        
        switch ($unit) {
            case 'minggu':
                return $value * 7;
            case 'bulan':
                return $value * 30;
            case 'tahun':
                return $value * 360;
            case 'hari':
            default:
                return $value;
        }
    }

    /**
     * Konversi dari total hari ke format human readable (nilai dan satuan)
     * Memprioritaskan satuan terbesar yang habis dibagi
     * 
     * @param int $days
     * @return array ['value' => int, 'unit' => string]
     */
    public static function dayToHuman($days)
    {
        $days = (int) $days;

        if ($days > 0 && $days % 360 === 0) {
            return ['value' => $days / 360, 'unit' => 'tahun'];
        }

        if ($days > 0 && $days % 30 === 0) {
            return ['value' => $days / 30, 'unit' => 'bulan'];
        }

        if ($days > 0 && $days % 7 === 0) {
            return ['value' => $days / 7, 'unit' => 'minggu'];
        }

        return ['value' => $days, 'unit' => 'hari'];
    }

    /**
     * Generate Filament form components for a duration field
     * 
     * @param string $fieldName The actual database column name (e.g. 'duration_in_days')
     * @param string $label The label for the value input (e.g. 'Durasi')
     * @return \Filament\Forms\Components\Group
     */
    public static function makeDurationGrid(string $fieldName, string $label = 'Durasi')
    {
        $valueField = $fieldName . '_value';
        $unitField = $fieldName . '_unit';

        return Group::make([
            \Filament\Forms\Components\Hidden::make($fieldName)
                ->dehydrateStateUsing(fn (Get $get) => self::humanToDay($get($valueField) ?? 0, $get($unitField) ?? 'hari')),
                
            Grid::make(2)->schema([
                \Filament\Forms\Components\TextInput::make($valueField)
                    ->label($label)
                    ->numeric()
                    ->required()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (\Filament\Forms\Components\TextInput $component, $state, $record) use ($fieldName) {
                        if ($record && $record->{$fieldName} !== null) {
                            $human = self::dayToHuman($record->{$fieldName});
                            $component->state($human['value']);
                        }
                    }),
                \Filament\Forms\Components\Select::make($unitField)
                    ->label('Satuan '.$label)
                    ->options([
                        'hari' => 'Hari',
                        'minggu' => 'Minggu',
                        'bulan' => 'Bulan',
                        'tahun' => 'Tahun',
                    ])
                    ->default('bulan')
                    ->required()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (\Filament\Forms\Components\Select $component, $state, $record) use ($fieldName) {
                        if ($record && $record->{$fieldName} !== null) {
                            $human = self::dayToHuman($record->{$fieldName});
                            $component->state($human['unit']);
                        }
                    }),
            ]),
        ]);
    }
}
