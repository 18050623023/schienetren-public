<?php

namespace App\Filament\Tenant\Resources\WorkScheduleOverrides\Schemas;

use App\Models\Tenant\EmployeeAssignment;
use App\Models\Tenant\Holiday;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;

class WorkScheduleOverrideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('created_by')->default(Auth::user()->id),
                Select::make('employee_assignment_id')
                    ->live()
                    ->relationship('employeeAssignment', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->employee->user->name.' - '.$record->branch->name)
                    ->required(),
                Select::make('work_shift_id')
                    ->relationship('workShift', 'name')
                    ->required(),
                DatePicker::make('date')
                    ->live()
                    ->default(Date::now())
                    ->disabled(fn (Get $get) => !$get('employee_assignment_id'))
                    ->afterStateUpdated(function ($state, Get $get) {
                        if($get('employee_assignment_id') != null){
                            $assignment = EmployeeAssignment::find($get('employee_assignment_id'));
                            if ($state) {
                                $holiday = Holiday::forBranchAndDate($assignment->branch_id, Carbon::parse($state))->get();
                                if($holiday->isNotEmpty()){
                                    Notification::make()
                                        ->title('Date is Holiday')
                                        ->danger()
                                        ->color('danger')
                                        ->send();
                                }
                            }
                            // dd($isHoliday);

                        }
                    })
                    // ->rules([
                    //     fn (Get $get) => Rule::unique('attendances')
                    //         ->where(fn ($query) => $query
                    //             ->where('employee_assignment_id', $get('employee_assignment_id'))
                    //         ),
                    // ])
                    ,
                Radio::make('is_working_days')
                    ->boolean()
                    ->inline(),
                Textarea::make('reason')
                    ->required()
                    ->columnSpan(2),
                Select::make('approved_by')
                    ->relationship('approvedBy', 'name')
                    ->visibleOn('view'),
                TextInput::make('approved_at')
                    ->visibleOn('view'),
                Select::make('rejected_by')
                    ->relationship('rejectedBy', 'name')
                    ->visibleOn('view'),
                TextInput::make('rejected_at')
                    ->visibleOn('view'),
                Select::make('created_by')
                    ->relationship('createdBy', 'name')
                    ->columnSpan(2)
                    ->visibleOn('view'),

            ]);
    }
}
