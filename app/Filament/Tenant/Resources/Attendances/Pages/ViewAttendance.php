<?php

namespace App\Filament\Tenant\Resources\Attendances\Pages;

use App\Filament\Forms\Components\GeofenceMap;
use App\Filament\Tenant\Resources\Attendances\AttendanceResource;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\EmployeeAssignment;
use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ViewAttendance extends ViewRecord
{
    protected static string $resource = AttendanceResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         EditAction::make(),
    //     ];
    // }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ambil instance record Attendance yang sedang dibuka
        $record = $this->getRecord();

        // Ambil data log menggunakan relasi (pastikan model Attendance punya relasi logs())
        // Menggunakan ?-> (Optional Chaining) agar aman jika data kosong
        $logIn = $record->logs->where('type', 'in')->first();
        $logOut = $record->logs->where('type', 'out')->first();

        // Manipulasi array $data dengan menyuntikkan data dari tabel attendance_logs
        $data['time_in'] = $logIn ? Carbon::parse($logIn->logged_at)->format('H:i') : '--:--';
        $data['time_out'] = $logOut ? Carbon::parse($logOut->logged_at)->format('H:i') : '--:--';

        $data['photo_in'] = $logIn ? $logIn->photo : null;
        $data['photo_out'] = $logOut ? $logOut->photo : null;

        $data['latitude_in'] = $logIn ? $logIn->latitude : null;
        $data['latitude_out'] = $logOut ? $logOut->latitude : null;

        $data['longitude_in'] = $logIn ? $logIn->longitude : null;
        $data['longitude_out'] = $logOut ? $logOut->longitude : null;

        $data['accuracy_in'] = $logIn?->accuracy;
        $data['accuracy_out'] = $logOut?->accuracy;

        $data['distance_in'] = $logIn?->distance;
        $data['distance_out'] = $logOut?->distance;

        $data['late_in'] = $logIn ? $logIn->late_minutes : null;
        $data['late_out'] = $logOut ? $logOut->late_minutes : null;

        $data['early_in'] = $logIn ? $logIn->early_minutes : null;
        $data['early_out'] = $logOut ? $logOut->early_minutes : null;

        $data['notes_out'] = $logOut ? $logOut->notes : null;


        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('attendance_type')
                    // ->disabled()
                    ->options([
                        'working_day' => 'Hari Kerja',
                        'off_day' => 'Libur Weekend',
                        'holiday' => 'Libur holiday',
                        'leave' => 'Cuti',
                        'override' =>'Override'
                    ])
                    ->columnSpanFull(),
                Section::make('Attendance Information')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('employee_assignment_id')
                            ->live()
                            ->searchable()
                            ->disabled(fn (Get $get) => !$get('date'))
                            ->options(function (Get $get, $record) {
                                // dd($record->logs->where('type', 'in')->first()->logged_at);
                                // 1. Ambil data tanggal dari form
                                $date = $get('date');

                                // 2. Cegah query berjalan jika tanggal belum diisi oleh user
                                if (! $date) {
                                    return [];
                                }

                                // 3. Ambil data employee assignment berdasarkan tanggal
                                return EmployeeAssignment::with('employee') // Load relasi employee
                                    ->where('start_date', '<=', $date)
                                    // ->where('end_date', '>=', $date)
                                    ->where(function ($query) use ($date) {
                                        $query->whereNull('end_date')
                                            ->orWhereDate('end_date', '>=', $date);
                                    })
                                    ->get() // Ambil datanya dulu dari database
                                    ->pluck('employee.name', 'id'); // Ambil nama dari relasi, dan gunakan employee_id sebagai value select
                            }),

                        TextInput::make('overtime')
                            ->placeholder('Overtime in minutes')
                            ->numeric()
                            ->hidden(fn (Get $get) => $get('type') == 'in')
                            ->disabled(fn (Get $get) => $get('type') == 'in'),

                        DatePicker::make('date'),
                        Textarea::make('notes')
                            ->hidden(fn (Get $get) => $get('type') == 'out')
                            ->placeholder(fn (Get $get) => $get('type') == 'in' ? 'Alasan mengisi absen manual' : 'Catatan untuk absensi pulang'),
                    ]),

                Section::make('Check In')
                    ->columns(2)
                    ->schema([
                        TimePicker::make('time_in'),
                        TextArea::make('notes_in'),
                        TextInput::make('late_in'),
                        TextInput::make('early_in'),
                        TextInput::make('accuracy_in'),
                        TextInput::make('distance_in'),
                        TextInput::make('latitude_in'),
                        TextInput::make('longitude_in'),
                        GeofenceMap::make('map_in')
                            ->columnSpanFull()
                            ->live()
                            ->latitudeField('latitude_in')
                            ->longitudeField('longitude_in')
                            // ->mode('admin')
                            // ->permanentCircle(fn (Get $get) => $get('latitude_in'), fn (Get $get) => $get('longitude_in')),
                    ]),
                Section::make('Check Out')
                    ->columns(2)
                    ->schema([
                        TimePicker::make('time_out'),
                        TextArea::make('notes_out'),
                        TextInput::make('late_out'),
                        TextInput::make('early_out'),
                        TextInput::make('accuracy_out'),
                        TextInput::make('distance_out'),
                        TextInput::make('latitude_out'),
                        TextInput::make('longitude_out'),
                        GeofenceMap::make('map_out')
                            ->columnSpanFull()
                            ->live()
                            ->latitudeField('latitude_out')
                            ->longitudeField('longitude_out')
                            // ->mode('admin')
                            // ->permanentCircle(fn (Get $get) => $get('latitude_in'), fn (Get $get) => $get('longitude_in')),
                    ]),

            ]);
    }
}
