<?php

namespace App\Filament\Tenant\Resources\Branches\Schemas;

use App\Filament\Forms\Components\GeofenceMap;
use Dom\Text;
use EduardoRibeiroDev\FilamentLeaflet\Fields\MapPicker;
use EduardoRibeiroDev\FilamentLeaflet\Enums\TileLayer;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Branch Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required(),

                        TextInput::make('code')
                            ->label('Branch Code')
                            ->unique('branches', 'code')
                            ->required(),

                        TextInput::make('phone')
                            ->required(),

                        Textarea::make('address')
                            ->required(),

                        Toggle::make('is_active')
                            ->required()
                            ->live()
                            ->rules([
                                fn(string $operation, ?\Illuminate\Database\Eloquent\Model $record) => function (string $attribute, $value, \Closure $fail) use ($operation, $record) {
                                    if ($value) {
                                        $check = \App\Models\Tenant\Branch::checkActiveLimit($operation === 'edit' ? $record : null);
                                        if (! $check->passed) {
                                            $fail($check->message);
                                        }
                                    }
                                },
                            ]),
                    ]),
                Section::make('Location')
                    ->columns(2)
                    ->schema([
                        TextInput::make('latitude')
                            ->required()
                            ->live()
                            ->default(fn () => (float) settingTenant('default_latitude', -7.258137)),

                        TextInput::make('longitude')
                            ->required()
                            ->live()
                            ->default(fn () => (float) settingTenant('default_longitude', 112.749306)),

                        TextInput::make('attendance_radius')
                            ->label('Radius Absensi (Meter)')
                            ->numeric()
                            ->default(fn () => (int) settingTenant('default_attendance_radius', 10))
                            ->required(),
                    ]),

                GeofenceMap::make('map')
                    ->columnSpanFull()
                    ->latitudeField('latitude')
                    ->longitudeField('longitude')
                    ->config([
                        'mode' => 'admin',
                    ])
                // ->permanentCircle(function (Get $get) {
                //     // $employee_assignment_id = $get('employee_assignment_id');
                //     // if (!$employee_assignment_id) {
                //     //     // Notification::make()
                //     //     //     ->danger()
                //     //     //     ->title('Please select employee first')
                //     //     //     ->send();
                //     // }

                //     // $assignment = EmployeeAssignment::with('branch')->find($employee_assignment_id);

                //     // if (!$assignment || !$assignment->branch) {
                //     //     Notification::make()
                //     //         ->danger()
                //     //         ->title('Branch location not found for this assignment')
                //     //         ->send();
                //     //     return null;
                //     // }

                //     return [
                //         -7.258137,
                //         112.749306,
                //         12 // berikan default jika null
                //     ];
                // })
                ,
            ]);
    }
}
