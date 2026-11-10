<?php

namespace App\Filament\Tenant\Resources\Attendances\Tables;

use App\Models\Tenant\Attendance;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->columns([
                TextColumn::make('employee_assignment_id')
                    ->label('Karyawan')
                    ->getStateUsing(fn($record) => $record->employeeAssignment->employee->user->name),
                TextColumn::make('date'),
                TextColumn::make('time_in')
                    ->getStateUsing(function ($record) {
                        $log = $record->logs?->where('type', 'in')->first();
                        return $log?->logged_at ? Carbon::parse($log->logged_at)->toTimeString() : '-';
                    }),
                TextColumn::make('time_out')
                    ->getStateUsing(function ($record) {
                        //  return Carbon::parse($record->logs?->where('type', 'in')->first()?->logged_at)->toTimeString();
                        $log = $record->logs?->where('type', 'out')->first();
                        return $log?->logged_at ? Carbon::parse($log->logged_at)->toTimeString() : '-';
                    }),
                TextColumn::make('attendance_type'),
                TextColumn::make('status'),
                TextColumn::make('notes'),
                TextColumn::make('overtime'),
                TextColumn::make('review_status')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        // Contoh: Jangan tampilkan badge status jika notes-nya kosong
                        if (!$record->requires_schedule_review) {
                            return 'Tidak perlu review'; // Akan membuat cell ini kosong
                        }

                        return $state;
                    })
                    ->colors([
                        'success' => 'approved',
                        'error' => 'rejected',
                        'warning' => 'pending',
                    ]),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    Action::make('approve')
                        ->label('Approve')
                        ->visible(fn($record) => $user?->can('approve', $record) && $record->requires_schedule_review)
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(
                            fn(Attendance $record) =>
                            $record->update([
                                'review_status' => 'approved',
                                'reviewed_by' => Auth::id(),
                            ])
                        ),
                    Action::make('reject')
                        ->label('Reject')
                        ->visible(fn($record) => $user?->can('approve', $record) && $record->requires_schedule_review)
                        ->icon('heroicon-o-x-circle')
                        ->color('error')
                        ->schema([
                            Textarea::make('note')
                                ->label('Reason Reject')
                                ->required()
                                ->rows(4)
                                ->maxLength(500),
                        ])
                        ->modalHeading('Reject Attendance Review')
                        ->modalDescription('Masukkan alasan penolakan.')
                        ->modalSubmitActionLabel('Reject')
                        ->action(function ($record, array $data) {
                            $record->update([
                                'review_status' => 'rejected',
                                'reviewed_by' => Auth::id(),
                                'notes' => $data['note'],
                            ]);
                        }),
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn($record) => $record->requires_schedule_review && ($record->review_status === 'pending' || $user->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])))),
                ]),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
