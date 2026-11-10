<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Pages\Attendance\AttendanceForm;
use App\Filament\Tenant\Pages\Attendance\AttendanceTable;
use App\Filament\Tenant\Pages\Attendance\HandlesAttendance;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\Holiday;
use App\Models\Tenant\LeaveRequest;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isFinite;

class AttendancePage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use HandlesAttendance;
    use InteractsWithTable;

    protected string $view = 'filament.tenant.pages.attendance-page';

    public ?array $data = [];


    public ?Carbon $loggedAt ;

    // variable for modal view
    public ?Attendance $selectedAttendance = null;
    public ?array $dataSelectedAttendance = [];
    // public function getHeading(): string
    // {
    //     return 'Attendance Report';
    // }

    // public function callAfterCapture(string $image): void
    // {
    //     dd($image);
    //     // $this->evaluate(
    //     //     $this->getAfterCapture(),
    //     //     [
    //     //         'image' => $image,
    //     //         'livewire' => $this->getLivewire(),
    //     //     ]
    //     // );
    // }

    public function __construct()
    {

        // $this->loggedAt = now();
        // $this->loggedAt = Carbon::parse('20:15:00');
        $this->loggedAt = Carbon::parse('08:13:00');
        $this->resetCurrentAssignmentAndAttendance();
    }


    public function mount(): void
    {
        $this->fillAttendanceForm();
    }

    public function fillAttendanceForm(): void
    {
        // dd($this->record);
        // $employee = Auth::user()->employees;
        $assignment = $this->getCurrentAssignment();
        $attendance = $this->getCurrentAttendance();
        // dd($assignment);
        $attendance_in =   $attendance?->logs?->where('type', 'in')->first();
        $attendance_out =   $attendance?->logs?->where('type', 'out')->first();

        $hasShiftManagement = tenant()->hasFeatureCode(\App\Enums\TenantFeature::SHIFT_MANAGEMENT);

        // $isWorkingDay = $assignment->isWorkingDay($this->loggedAt);
        $isWorkingDay = $hasShiftManagement
            ? $assignment->isWorkingDay($this->loggedAt)
            : ((int) $this->loggedAt->dayOfWeekIso <= 6);

        $isHoliday = Holiday::forBranchAndDate($assignment->branch_id, $this->loggedAt)->exists();

        // $isWorkScheduleOverride = \App\Models\Tenant\WorkScheduleOverride::approvedForAssignmentAndDate($assignment->id, $this->loggedAt)->exists();
        // $overrideIsWorkingDay = \App\Models\Tenant\WorkScheduleOverride::approvedForAssignmentAndDate($assignment->id, $this->loggedAt)->value('is_working_days') ?? false;
        
        $isWorkScheduleOverride = $hasShiftManagement
            && \App\Models\Tenant\WorkScheduleOverride::approvedForAssignmentAndDate($assignment->id, $this->loggedAt)->exists();
        $overrideIsWorkingDay = $isWorkScheduleOverride
            ? (\App\Models\Tenant\WorkScheduleOverride::approvedForAssignmentAndDate($assignment->id, $this->loggedAt)->value('is_working_days') ?? false)
            : false;

        // Tentukan status hari kerja berdasarkan kondisi override
        // 1. Tentukan status akhir apakah hari ini dianggap sebagai hari kerja
        $currentWorkingDay = $isWorkScheduleOverride
            ? $overrideIsWorkingDay
            : ($isWorkingDay && !$isHoliday);

        // 2. Jika status akhirnya adalah BUKAN hari kerja, buka modal
        if (!$currentWorkingDay) {
            $this->dispatch('open-modal', id: 'not-workingDay');
        }

        // dd($currentWorkingDay);

        $type = null;

        if($attendance_in === null && $attendance_out === null) {
            $type = 'in';
        }

        if($attendance_in !== null && $attendance_out === null) {
            $type = 'out';
        }

        $leave = LeaveRequest::query()
            ->where('employee_id', $assignment->employee_id)
            ->where('status', "approved")
            ->whereDate('start_date', '<=', $this->loggedAt)
            ->whereDate('end_date', '>=', $this->loggedAt)
            ->exists();

        // dd($attendance);

        $this->form->fill([
            // 'working_day' => $isWorkingDay,
            'leave' => $leave,
            'type' => $type !== null ? $type : 'present',
            'employee_assignment_id' => $assignment?->id,
            'assignment' => $assignment?->employee?->name,
            'date' => $this->loggedAt,
            'latitude' => $assignment?->branch?->latitude,
            'longitude' => $assignment?->branch?->longitude,
            'attendance_radius' => $assignment?->branch?->attendance_radius,
            'status'    => $attendance?->status,

            // 'time_in'   => Carbon::parse($attendance_in?->logged_at)->format('H:i:s'),
            // 'accuracy_in' => $attendance_in?->accuracy,
            // 'distance_in' => $attendance_in?->distance,
            // 'late_minutes_in' => $attendance_in?->late_minutes,
            // 'early_minutes_in' => $attendance_in?->early_minutes,
            // 'latitude_in' => $attendance_in?->latitude,
            // 'longitude_in' => $attendance_in?->longitude,

            'time_out' => $attendance_out?->logged_at,
        ]);
    }


    // public function submit()
    // {
    //         // dd($this->form->getState());
    //         return true;

    // }

    public function form(Schema $schema): Schema
    {

        return $schema
            ->statePath('data')
            ->columns(3)
            ->components([
                // Hidden::make('working_day'),
                Hidden::make('leave'),
                Hidden::make('type')
                    ->default('in'),
                Hidden::make('employee_assignment_id'),
                Hidden::make('attendance_radius'),
                TextInput::make('assignment')
                    // ->columnSpan(2)
                    ->disabled()
                    ->dehydrated(false),

                // Text::make('Status')
                //     ->color('warning')
                //     ->badge(),
                ViewField::make('status')
                    // ->columnSpan(2)
                    ->default('absent')
                    ->dehydrated(false)
                    ->view('filament.forms.components.attendance-status'),
                DatePicker::make('date')
                    // ->columnSpan(2)
                    ->disabled()
                    ->dehydrated(true),
                // AttendanceForm::information(),
                AttendanceForm::attendance(),
            ]);
    }


    public function table(Table $table): Table
    {
        // dd($this->getAttendanceQuery()->get());
        return $table
            ->query(
                    Attendance::query()
                        ->whereHas('employeeAssignment.employee', function ($query) {
                            $query->whereKey(Auth::user()->employees?->id);
                        })
                        ->with([
                            'employeeAssignment' => function ($query) {
                                $query->with('employee');
                            },
                            'logs'
                        ])
                        ->orderBy('date', 'desc')
                )
            ->columns(AttendanceTable::tableColumns())
            ->searchDebounce('750ms')
            ->searchOnBlur()
            ->recordActions([
                Action::make('detail')
                    ->action(function (Attendance $record) {
                        $this->openPhotoModal($record);
                        // $record->is_featured = true;
                        // $record->save();
                    }),
                // Action::make('unfeature')
                //     ->action(function (Post $record) {
                //         $record->is_featured = false;
                //         $record->save();
                //     })
                //     ->visible(fn (Post $record): bool => $record->is_featured),
            ])
            ;
    }

    // function for modal view
    // public function openPhotoModal(Attendance $attendance): void
    // {
    //     $this->selectedAttendance = $attendance;
    //     // $this->dataSelectedAttendance = $attendance->toArray();
    //     // dd($attendance->employeeAssignment->employee->name);
    //     $attendance_in = $attendance->logs?->where('type', 'in')->first();
    //     $attendance_out = $attendance->logs?->where('type', 'out')->first();

    //     $this->formModal->fill([
    //         'name' => $attendance->employeeAssignment->employee->name,
    //         'status' => $attendance->status,
    //         'date' => $attendance->date,

    //         'time_in' => $attendance_in?->logged_at,
    //         'time_out' => $attendance_out?->logged_at,

    //         'late_minutes_in' => $attendance_in?->late_minutes,
    //         'early_minutes_in' => $attendance_in?->early_minutes,

    //         'late_minutes_out' => $attendance_out?->late_minutes,
    //         'early_minutes_out' => $attendance_out?->early_minutes,

    //         'accuracy_in' => $attendance_in?->accuracy,
    //         'accuracy_out' => $attendance_out?->accuracy,

    //         'distance_in' => $attendance_in?->distance,
    //         'distance_out' => $attendance_out?->distance,

    //         'latitude_in' => $attendance_in?->latitude,
    //         'latitude_out' => $attendance_out?->latitude,

    //         'longitude_in' => $attendance_in?->longitude,
    //         'longitude_out' => $attendance_out?->longitude,

    //         'photo_in' => $attendance_in?->photo,
    //         'photo_out' => $attendance_out?->photo,
    //     ]);

    //     // dd($this->formModal->getState());

    //     $this->dispatch('open-modal', id: 'attendance-photo');
    // }

    public function openPhotoModal(Attendance $attendance): void
    {
        $this->selectedAttendance = $attendance;

        $employee = $attendance->employeeAssignment->employee;
        $logs = $attendance->logs;

        $attendanceIn = $logs?->firstWhere('type', 'in');
        $attendanceOut = $logs?->firstWhere('type', 'out');

        $this->formModal->fill([
            'name' => $employee->name,
            'status' => $attendance->status,
            'date' => $attendance->date,

            ...$this->mapAttendanceLog($attendanceIn, 'in'),
            ...$this->mapAttendanceLog($attendanceOut, 'out'),
        ]);

        $this->dispatch('open-modal', id: 'attendance-photo');
    }

    private function mapAttendanceLog(?AttendanceLog $log, string $prefix): array
    {
        return [
            "time_{$prefix}" => $log?->logged_at,
            "late_minutes_{$prefix}" => $log?->late_minutes,
            "early_minutes_{$prefix}" => $log?->early_minutes,
            "accuracy_{$prefix}" => $log?->accuracy,
            "distance_{$prefix}" => $log?->distance,
            "latitude_{$prefix}" => $log?->latitude,
            "longitude_{$prefix}" => $log?->longitude,
            "photo_{$prefix}" => $log?->photo,
        ];
    }

    public function formModal(Schema $schema): Schema
    {
        // dd($this->selectedAttendance->employeeAssignment->employee->name);
        // dd($this->dataSelectedAttendance);

        // $this->selectedAttendance->fill();
        return $schema
            ->statePath('dataSelectedAttendance')
            ->columns(6)
            ->components([
                // AttendanceForm::information(),
                TextInput::make('name')
                    // ->hidden(function (Get $get, $record) {
                    //     dd($record);
                    // })
                    ->columnSpan(2)
                    ->disabled()
                    ->dehydrated(false),

                // Text::make('Status')
                //     ->color('warning')
                //     ->badge(),
                ViewField::make('status')
                    ->columnSpan(2)
                    ->default('absent')
                    ->view('filament.forms.components.attendance-status'),

                DatePicker::make('date')
                    ->columnSpan(2)
                    ->disabled()
                    // ->dehydrated(true)
                    ,
                AttendanceForm::informationSection('in'),
                AttendanceForm::informationSection('out'),
                // Section::make()
                //     ->label('Information')
                //     ->columnSpan(2)
                //     ->columns(2)
                //     // ->hidden(function (AttendancePage $livewire) {
                //     //         dd($livewire->hasCheckInLog('in'));
                //     //         return !$livewire->hasCheckInLog('in');
                //     //     })
                //     ->schema([
                //     ])
            ])
            ;
    }

    public static function canAccess(): bool
    {
        return !Auth::user()?->hasRole('owner');
    }

}
