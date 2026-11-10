<?php

namespace App\Filament\Tenant\Resources\AttendanceCorrections\Schemas;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\EmployeeAssignment;
use DateTime;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AttendanceCorrectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('requested_by')
                    ->default(Auth::user()->id)
                    ->required(),
                Hidden::make('date')
                    ->required()
                    ->live(),
                Select::make('attendance_id')
                    ->label('Attendance')
                    ->columnSpanFull()
                    ->relationship('attendance', 'date')
                    ->live()
                    ->required()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $attendance = Attendance::find($state);
                        // dd($attendance->date);
                        $set('date', $attendance->date);
                    }),
                Select::make('employee_assignment_id')
                    ->live()
                    ->searchable()
                    ->disabled(fn (Get $get) => !$get('date'))
                    ->options(function (Get $get) {
                        // 1. Ambil data tanggal dari form
                        $date = $get('date');

                        // 2. Cegah query berjalan jika tanggal belum diisi oleh user
                        if (! $date) {
                            return [];
                        }

                        // 3. Ambil data employee assignment berdasarkan tanggal
                        return EmployeeAssignment::with('employee') // Load relasi employee
                            ->where('start_date', '<=', $date)
                            ->where(function ($query) use ($date) {
                                $query->whereNull('end_date')
                                    ->orWhereDate('end_date', '>=', $date);
                            })
                            ->get() // Ambil datanya dulu dari database
                            ->pluck('employee.name', 'id'); // Ambil nama dari relasi, dan gunakan employee_id sebagai value select
                    }),

                Select::make('status')
                    ->live()
                    ->default('pending')
                    ->hidden(fn () => !Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])))
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Grid::make()
                    ->columns(2)
                    ->columnSpanfull()
                    ->schema([
                        TimePicker::make('actual_time_in')
                            ->required()
                            ->dehydrateStateUsing(fn ($state) =>
                                now()->setTimeFromTimeString($state)
                            ),
                        TimePicker::make('actual_time_out')
                            ->required()
                            ->dehydrateStateUsing(fn ($state) =>
                                now()->setTimeFromTimeString($state)
                            ),
                    ]),
                Textarea::make('reason')
                    // ->hidden(fn () => Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])))
                    ->columnSpan(fn () => !Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))? 2 : 1)
                    ->required(),
                Textarea::make('review_notes')
                    ->live()
                    ->required(fn (Get $get) => Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])) || $get('status') != 'pending')
                    ->hidden(fn (Get $get) => !Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])) || $get('status') == 'pending'),

                Select::make('requested_by')
                    ->visibleOn('view')
                    ->relationship('requestedBy', 'name'),

                Select::make('reviewed_by')
                    ->visibleOn('view')
                    ->relationship('reviewedBy', 'name'),

                DateTimePicker::make('reviewed_at')
                    ->visibleOn('view'),

            ]);
    }
}
