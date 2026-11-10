<?php

namespace App\Filament\Tenant\Resources\Employees\RelationManagers;

use App\Filament\Tenant\Resources\EmployeeAssignments\EmployeeAssignmentResource;
use App\Filament\Tenant\Resources\WorkSchedules\WorkScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

class EmployeeAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'EmployeeAssignments';

    protected static ?string $relatedResource = EmployeeAssignmentResource::class;

    // #[Override]
    // public function form(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([

    //         ])
    // }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('roles.name')
                    ->badge()
                    ->label('Role')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable(),
                TextColumn::make('workSchedule.name')
                    ->label('Work Schedule')
                    ->searchable()
                // ->action(
                //     fn ($record) => WorkScheduleResource::getUrl('view', [
                //         'record' => $record->workSchedule,
                //     ])
                // )
                // ->color('primary')
                // ->url(fn ($record) => WorkScheduleResource::getUrl('view', [
                //     'record' => $record->workSchedule,
                // ]))
                ,
                TextColumn::make('start_date')
                    ->searchable(),
                TextColumn::make('end_date')
                    ->placeholder('Masih Aktif')
                    ->searchable(),

            ])
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    ->before(
                        function (CreateAction $action, array $data) {
                            if (\App\Models\Tenant\EmployeeAssignment::hasActiveAssignment($this->ownerRecord->id, $data['branch_id'])) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Assignment aktif sudah ada.')
                                    ->danger()
                                    ->send();
                                $action->halt();
                            }
                        }
                    ),
            ])
            ;
    }
}
