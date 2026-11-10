<?php

namespace App\Filament\Tenant\Resources\Compensation\RelationManagers;

use App\Models\Tenant\CompensationAssignment;
use App\Models\Tenant\EmployeeAssignment;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    public function formPackageAssignments(): Array
    {
        return [
            Select::make('recordId')
                ->label('Assignment')
                ->searchable()
                ->live()
                ->preload()
                ->required()
                ->visibleOn(['attach'])
                ->options(
                    EmployeeAssignment::with('employee')
                    ->get()
                    ->mapWithKeys(fn ($assignment) => [
                        $assignment->id => $assignment->employee->name." ".$assignment->branch->name,
                    ])
                )
                ->afterStateUpdated(function ($state, Set $set) {
                    if($state != null){
                        $assignment = CompensationAssignment::where('employee_assignment_id', $state)->exists();
                        if($assignment){
                            Notification::make()
                                ->title('Assignment sudah ada')
                                ->danger()
                                ->send();

                            // Kosongkan field karena duplikat
                            $set('recordId', null);
                        }
                    }
                }),

            DatePicker::make('start_date')
                ->required(),

            DatePicker::make('end_date')
                ->helperText('Kosongkan jika karyawan tetap.'),
        ];
    }
    // public function form(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([

    //             DatePicker::make('start_date'),
    //             DatePicker::make('end_date'),
    //             // TextInput::make('employee_assignment_id')
    //             //     ->required()
    //             //     ->maxLength(255),
    //         ]);
    // }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('employee.name'),
                TextColumn::make('pivot.start_date')
                    ->label('Start Date'),
                TextColumn::make('pivot.end_date')
                    ->label('End Date')
                    ->placeholder('Masih Aktif')
                    ->color(fn ($state) => $state ? 'gray' : 'success'),
                // TextColumn::make('employee_assignment_id')
                //     ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(),
                AttachAction::make()
                    ->schema($this->formPackageAssignments()),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema($this->formPackageAssignments()),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DetachBulkAction::make(),
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
