<?php

namespace App\Filament\Tenant\Pages\Attendance;

use App\Filament\Forms\Components\GeofenceMap;
use App\Filament\Forms\Components\TakePicture;
use App\Filament\Tenant\Pages\AttendancePage;
use App\Services\AttendanceService;
use App\Services\GeoLocationService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\EmptyState;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;
use App\Enums\TenantFeature;
use Filament\Support\Icons\Heroicon;

class AttendanceForm  /* extends AttendancePage */
{

    public static function information(): Section
    {
        // return Section::make('Information')
        return Section::make()
            ->label('Information')
            ->columnSpan(2)
            // ->columns(2)
            // ->hidden(function (AttendancePage $livewire) {
            //         dd($livewire->hasCheckInLog('in'));
            //         return !$livewire->hasCheckInLog('in');
            //     })
            ->schema([
                self::informationSection('in'),
                self::informationSection('out'),
                // EmptyState::make('No users yet')
                //     ->columnSpan(3)
                // //     ->description('Get started by creating a new user.')
                // //     ->icon(Heroicon::OutlinedUser)
                //     ->hidden(fn (Get $get) =>
                //         $get("time_out") === null
                //     )

            ]);
    }

    public static function informationSection(string $type): Section
    {
        return Section::make($type === 'in' ? 'Check In' : 'Check Out')
            ->columnSpan(3)
            ->columns(2)
            // ->hidden(fn (Get $get) =>
            //     $get("time_{$type}") === null
            // )
            // ->hidden(
            //     fn (Get $get) => $type === 'in'
            //             ? $get('type') === 'in'
            //             : $get('type') === 'out'
            // )
            ->schema([
                EmptyState::make('Belum Check Out')
                    ->columnSpanFull()
                    // ->description('Get started by creating a new user.')
                    ->icon(Heroicon::OutlinedUser)
                    ->hidden(fn (Get $get) =>
                        !($get("time_{$type}") === null)
                    ),
                // Text::make('Time'),
                // Text::make(function (Get $get) use($type) {
                //     return ": ".$get('time_'.$type);
                //     // return $get('type') === 'in' ? 'Check In' : 'Check Out';
                // })
                // ->columnSpan(2),

                // Text::make('Late')
                //     ->hidden(fn (Get $get) => $get("late_minutes_{$type}") === null)
                // ,
                // Text::make(function (Get $get) use($type) {
                //     return ": ". $get("late_minutes_{$type}") . ' minute';
                // })
                //     ->hidden(fn (Get $get) => $get("late_minutes_{$type}") === null)
                // ,

                // Text::make('Early')
                //     ->hidden(fn (Get $get) => $get("early_minutes_{$type}") === null),
                // Text::make(function (Get $get) use($type) {
                //     return ": ". $get("early_minutes_{$type}") . ' minute';
                // })
                // ->hidden(fn (Get $get) => $get("early_minutes_{$type}") === null)
                // ,
                TimePicker::make("time_{$type}")
                    ->disabled(),

                TextInput::make("late_minutes_{$type}")
                    ->disabled()
                    ->default(0)
                    ->suffix('minute')
                    ->hidden(fn (Get $get) => $get("early_minutes_{$type}") != null),
                TextInput::make("early_minutes_{$type}")
                    ->disabled()
                    ->suffix('minute')
                    ->hidden(fn (Get $get) => $get("early_minutes_{$type}") === null),

                // Text::make(function (Get $get) use($type) {
                //     return "Accuracy : ". $get("accuracy_{$type}") . ' meter';
                // }),

                // Text::make(function (Get $get) use($type) {
                //     return "Distance : ". $get("distance_{$type}") . ' meter';
                // }),

                TextInput::make("accuracy_{$type}")
                    ->disabled(),
                TextInput::make("distance_{$type}")
                    ->disabled(),
                TextInput::make("latitude_{$type}")
                    ->numeric()
                    ->disabled(),
                TextInput::make("longitude_{$type}")
                    ->numeric()
                    ->disabled(),
                GeofenceMap::make("map_{$type}")
                    ->columnSpanFull()
                    ->live()
                    ->latitudeField("latitude_{$type}")
                    ->longitudeField("longitude_{$type}")
                    ->hidden(function (Get $get) use($type) {
                        // dd($get("latitude_{$type}"));
                        return $get("latitude_{$type}") == null || $get("longitude_{$type}") == null;
                    })

                    ->config(fn (Get $get) => [
                            // 'mode' => 'admin',
                            'zoom' => 17,
                            'center' => [
                                $get("latitude_{$type}"),
                                $get("longitude_{$type}")
                            ],
                            'height' => 200
                        ]
                    ),
            ]);
    }

    public static function attendance(): Section
    {
        // return Section::make(fn (Get $get) => $get('type') === 'in' ? 'Check In' : 'Check Out')
        return Section::make()
            ->heading(function (Get $get) {
                return new HtmlString(
                    '<div class="text-center w-full font-semibold text-lg">
                        <span>'.($get('type') === 'in' ? 'Check In' : 'Check Out').'</span>
                    </div>'
                );
            })
            ->hidden(function (AttendancePage $livewire, Get $get) {
                    // dd($get('employee_assignment_id'));
                    if($get('employee_assignment_id') == null){
                        Notification::make()
                            ->danger()
                            ->title('Anda belum memiliki assignment')
                            ->body('Hubungi HR untuk mendapatkan assignment')
                            ->send();
                    }

                    if ($get('leave')) {
                        Notification::make()
                            ->title('Pengajuan cuti sudah di Approve.')
                            ->danger()
                            ->send();
                    }
                    // dd($get('leave'));
                    return $get('leave') || $livewire->hasCheckInLog('out') || $get('employee_assignment_id') == null;
                })
            ->columnSpanFull()
            // ->columnSpan(fn (Get $get) => $get('type') === 'in' ? 6 : 4)
            // ->columns(fn (Get $get) => $get('type') === 'in' ? 2 : 1)
            ->columns(2)
            ->schema([
                // Section::make('Location')
                Section::make()
                    ->label('Location')
                    // ->collapsible()
                    // ->columnSpanFull()
                    ->columns(2)
                    // ->columnSpan(fn (Get $get) => $get('type') === 'in' ? 1 : 2 )
                    ->schema([

                        TextInput::make('latitude')
                            ->required()
                            ->live()
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('longitude')
                            ->required()
                            ->live()
                            ->disabled()
                            ->dehydrated(),

                        GeofenceMap::make('map')
                            ->columnSpanFull()
                            // ->dehydrated(false)
                            ->live()
                            ->latitudeField('latitude')
                            ->longitudeField('longitude')
                            ->afterStateUpdated(function ($state, Get $get, AttendancePage $livewire) {
                                if (tenant()->hasFeatureCode(TenantFeature::ATTENDANCE_GEOFENCE)) {
                                    // Cek Akurasi GPS
                                    if (isset($state['accuracy']) && $state['accuracy'] > 150) {
                                        Notification::make()
                                            ->danger()
                                            ->title('Akurasi GPS Lemah')
                                            ->body('Sinyal GPS Anda meleset sejauh ' . $state['accuracy'] . ' meter. Mohon pindah ke area terbuka.')
                                            ->send();
                                        return null;
                                    }

                                    // Cek Jarak Radius
                                    // if($state['distance'] > $get('attendance_radius')){
                                    if (isset($state['distance']) && $state['distance'] > $get('attendance_radius')) {
                                        $livewire->dispatch('open-modal', id: 'distance-modal');
                                        Notification::make()
                                            ->danger()
                                            ->title('You are outside the attendance radius')
                                            ->body('Jarak anda ' . app(GeoLocationService::class)->formatDistance($state['distance']))
                                            ->send();
                                        return null;
                                    }
                                }
                            })
                            ->config(function (Get $get) {
                                return [
                                    'mode' => 'attendance',
                                    'zoom' => 13,
                                    'center' => [-7.2600, 112.7550],
                                    'height' => 200
                                ];
                            })
                            ->permanentCircle(function (Get $get, $state, AttendancePage $livewire) {
                                // dd($this->form->getState());

                                $assignment = $livewire->getCurrentAssignment();
                                // $assignment = EmployeeAssignment::with('branch')->find($get('employee_assignment_id'));

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

                    ])
                    // ->afterStateUpdated(function () {
                    //     dd($this->form->getState());
                    //     // $livewire->savePhoto($image);
                    // })
                    ,
                TakePicture::make('photo')
                    ->live()
                    ->hidden(function (Get $get) {
                        if (!tenant()->hasFeatureCode(TenantFeature::ATTENDANCE_GEOFENCE)) {
                            return false;
                        }

                        $distance = data_get($get('map'), 'distance');
                        return $distance > $get('attendance_radius');
                    })
                    // ->columnSpan(fn (Get $get) => $get('type') === 'in' ? 1 : 2 )
                    ->afterStateUpdated(function ($state, Get $get, AttendancePage $livewire) {
                        // dd($state);
                        $livewire->submitAttendance();
                    })
                    // ->label('Take Picture')
                    // ->disabled()
                    ->config([
                        // 'placeholder' => 'Mulai Ambil Foto Absen',
                        // 'AspectRatio' => '16:9',
                        // 'showPreview' => false
                    ]),
            ]);
    }
}

?>
