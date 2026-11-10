<?php

namespace App\Filament\Tenant\Resources\WorkScheduleOverrides\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class WorkScheduleOverridesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employeeAssignment.employee.user.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('employeeAssignment.branch.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('workShift.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('date')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('approval_status')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->color(fn (string $state): string => match ($state) {
                        // 'draft' => 'gray',
                        'approved' => 'success',
                        'rejected' => 'error',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->approval_status !== 'approved')
                    ->action(function ($record) {
                        $record->update([
                            'approval_status' => 'approved',
                            'approved_by' => Auth::user()->id,
                            'approved_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Schedule approved')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->approval_status !== 'rejected')
                    ->action(function ($record) {
                        $record->update([
                            'approval_status' => 'rejected',
                            'rejected_by' => Auth::user()->id,
                            'rejected_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Schedule rejected')
                            ->danger()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
