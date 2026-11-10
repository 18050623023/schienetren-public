<?php

namespace App\Filament\Tenant\Resources\Attendances\Schemas;

use App\Filament\Forms\Components\GeofenceMap;
use App\Models\Tenant\EmployeeAssignment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Date;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(function (Get $get) {
                        return $get('type') === 'in' ? 'Check In' : 'Check Out';
                    })
                    ->columns(2)
                    ->schema([

                        DatePicker::make('date')
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                // SANGAT PENTING: Begitu tanggal berubah, reset form state di bawahnya
                                // agar komponen mengevaluasi ulang kondisinya secara bersih.
                                $set('employee_assignment_id', null);
                                $set('latitude', null);
                                $set('longitude', null);
                                $set('type', 'in'); // kembalikan ke default 'in' sebelum user memilih karyawan
                            }),

                        TimePicker::make('time')
                            ->default(Date::now()),

                        TextInput::make('late_minutes')
                            ->numeric()
                            ->required(),

                        TextInput::make('early_minutes')
                            ->numeric()
                            ->required(),

                        FileUpload::make('photo')
                            ->columnSpan(2)
                            ->image()       // ini agar hanya menerima gambar
                            ->label('Upload Gambar absensi')
                            ->hidden(function (Get $get) {
                                if (!$get('employee_assignment_id')) {
                                    return true;
                                }
                                // $employee_assignment = EmployeeAssignment::findOrFail($get('employee_assignment_id'));
                                // dd($employee_assignment->employee_id);
                                return false;
                            })
                            ->directory(function (Get $get) {
                                if (!$get('employee_assignment_id')) {
                                    // Notification::make()
                                    //     ->danger()
                                    //     ->title('Please select employee first')
                                    //     ->send();
                                }

                                $employee_assignment = EmployeeAssignment::findOrFail($get('employee_assignment_id'));

                                return 'images/attendance/'.$employee_assignment->employee_id.'/';
                            }) // folder penyimpanan
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file, Get $get): string => 'attendance-'.$get('date').'-'.$get('type').'.'.$file->getClientOriginalExtension(),
                            )
                            ->visibility('public'),
                    ]),

                Section::make('Attendance Information')
                    ->columns(2)
                    ->schema([

                        TextInput::make('type')
                            ->live()
                            ->columnSpanFull()
                            ->default('in')
                            ->disabled()
                            ->dehydrated(true),
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
                            })
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!$state) {
                                    $set('latitude', null);
                                    $set('longitude', null);
                                    $set('type', 'in');
                                    return;
                                }

                                // Cukup 1 query efisien dengan eager loading relasi 'branch'
                                $assignment = EmployeeAssignment::with([
                                        'branch',
                                        'attendance' => function ($query) use ($get) {
                                            $query->with('logs')->whereDate('created_at', $get('date'));
                                        }
                                    ])->find($state);

                                if ($assignment && $assignment->branch) {
                                    // Set value field latitude & longitude secara dinamis
                                    $set('latitude', $assignment->branch->latitude);
                                    $set('longitude', $assignment->branch->longitude);
                                } else {
                                    Notification::make()
                                        ->danger()
                                        ->title('Branch location not found for this assignment')
                                        ->send();
                                }

                                // dd($assignment->attendance->first()->logs->count());

                                // 3. LOGIKA UTAMA: Cek tipe absensi (IN / OUT)
                                // Menggunakan ->count() dari koleksi hasil eager loading (tidak memicu query SQL baru)
                                if ($assignment->attendance?->first()?->logs?->count() > 0) {
                                    // Jika sudah ada absensi hari ini, set type jadi 'out'
                                    $set('type', 'out');
                                } else {
                                    // Jika belum ada absensi hari ini, set type jadi 'in'
                                    $set('type', 'in');
                                }

                            }),

                        Select::make('status')
                            ->hidden(fn (Get $get) => $get('type') == 'out')
                            ->disabled(fn (Get $get) => $get('type') == 'out')
                            ->options([
                                'present' => 'Present',
                                'absent' => 'Absent',
                                'late' => 'Late',
                                'early' => 'Early',
                            ]),
                        TextInput::make('overtime')
                            ->placeholder('Overtime in minutes')
                            ->numeric()
                            ->hidden(fn (Get $get) => $get('type') == 'in')
                            ->disabled(fn (Get $get) => $get('type') == 'in'),

                        Textarea::make('notes')
                            ->hidden(fn (Get $get) => $get('type') == 'out')
                            ->placeholder(fn (Get $get) => $get('type') == 'in' ? 'Alasan mengisi absen manual' : 'Catatan untuk absensi pulang')
                            ->columnSpan(2),
                    ]),

                Section::make('Location')
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([

                        TextInput::make('latitude')
                            ->required()
                            ->live(),

                        TextInput::make('longitude')
                            ->required()
                            ->live(),
                        GeofenceMap::make('map')
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->live('debounce:100')
                            ->latitudeField('latitude')
                            ->longitudeField('longitude')
                            ->config([
                                'mode' => 'admin',
                            ])
                            ->visible(function (Get $get) {
                                if (!$get('employee_assignment_id')) {
                                    return false;
                                }

                                if (!$get('latitude')) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Please fill location first or set default location')
                                        ->send();
                                    return false;
                                }
                                return true;
                            })
                            ->permanentCircle(function (Get $get) {
                                $employee_assignment_id = $get('employee_assignment_id');
                                if (!$employee_assignment_id) {
                                    // Notification::make()
                                    //     ->danger()
                                    //     ->title('Please select employee first')
                                    //     ->send();
                                }

                                $assignment = EmployeeAssignment::with('branch')->find($employee_assignment_id);

                                if (!$assignment || !$assignment->branch) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Branch location not found for this assignment')
                                        ->send();
                                    return null;
                                }

                                return [
                                    $assignment->branch->latitude,
                                    $assignment->branch->longitude,
                                    $assignment->branch->attendance_radius ?? 100 // berikan default jika null
                                ];
                            }),

                    ]),
            ]);
    }
}
