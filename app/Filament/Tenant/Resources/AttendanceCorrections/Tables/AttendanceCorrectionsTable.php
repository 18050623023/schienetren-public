<?php

namespace App\Filament\Tenant\Resources\AttendanceCorrections\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AttendanceCorrectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->getStateUsing(fn ($record) => $record->employeeAssignment->employee->user->name),
                TextColumn::make('date'),
                TextColumn::make('actual_time_in')
                    ->time(),
                TextColumn::make('actual_time_out')
                    ->time(),
                TextColumn::make('status')
                    ->searchable()
                    ->badge()
                    ->colors([
                        'success' => 'approved',
                        'error' => 'rejected',
                        'warning' => 'pending',
                    ]),
                TextColumn::make('reason'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->visible(fn ($record) => $record->status === 'pending' && Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Textarea::make('note')
                            ->label('Reason')
                            ->required()
                            ->rows(4)
                            ->maxLength(500),
                    ])
                    ->modalHeading('Approve Attendance Correction')
                    ->modalDescription('Masukkan alasan appreoval.')
                    ->modalSubmitActionLabel('Aprove')
                    ->action(function ($record, array $data) {
                        // dd($data);
                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => Auth::user()->id,
                            'reviewed_at' => now(),
                            'review_notes' => $data['note'],
                        ]);
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->visible(fn ($record) => $record->status === 'pending' && Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('note')
                            ->label('Reason Reject')
                            ->required()
                            ->rows(4)
                            ->maxLength(500),
                    ])
                    ->modalHeading('Reject Attendance Correction')
                    ->modalDescription('Masukkan alasan penolakan.')
                    ->modalSubmitActionLabel('Reject')
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'reviewed_by' => Auth::user()->id,
                            'reviewed_at' => now(),
                            'review_notes' => $data['note'],
                        ]);
                    }),
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => $record->status === 'pending' || Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
