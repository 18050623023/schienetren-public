<?php

namespace App\Filament\Tenant\Pages\Attendance;

use App\Filament\Tenant\Pages\AttendancePage;
use App\Models\Tenant\Attendance;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class AttendanceTable
{

    public static function tableColumns(): array
    {
        return [
                TextColumn::make('date')
                    ->searchable()
                    ->sortable()
                    // ->action(function (Attendance $record, AttendancePage $livewire): void {
                    //     // dd($record);
                    //     $livewire->openPhotoModal($record);

                    // })
                    ,
                TextColumn::make('Branch')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->employeeAssignment->branch->name),
                TextColumn::make('status')
                    ->sortable()
                    ->badge()
                    ->getStateUsing(function ($record) {
                        // dd($record);
                        if ($record->status == 'off') {
                            return 'Off '.$record->attendance_type;
                        }
                        return $record->status;
                        // return Carbon::parse($record->logs?->where('type', 'in')->first()?->logged_at)->toTimeString();
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'early' => 'success',
                        'present' => 'primary',
                        'late' => 'danger',
                        'absent' => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('Check In')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return Carbon::parse($record->logs?->where('type', 'in')->first()?->logged_at)->toTimeString();
                    }),
                TextColumn::make('Check Out')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $logged_at = $record->logs?->where('type', 'out')->first()?->logged_at;
                        return $logged_at ? Carbon::parse($record->logs?->where('type', 'out')->first()?->logged_at)->toTimeString(): '--:--';
                    }),
                TextColumn::make('overtime')
                    ->sortable(),
                // TextColumn::make('notes')
                //     ->searchable(),
                // TextColumn::make('Note Out')
                //     ->searchable()
                //     ->getStateUsing(fn ($record) => $record->logs?->where('type', 'out')->first()?->notes),
            ];
    }
}
?>
