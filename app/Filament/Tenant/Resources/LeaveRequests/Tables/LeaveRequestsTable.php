<?php

namespace App\Filament\Tenant\Resources\LeaveRequests\Tables;

use App\Filament\Tenant\Resources\LeaveRequests\Action\LeaveRequestActionStatus;
use App\Models\Tenant\LeaveRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LeaveRequestsTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->columns([
                TextColumn::make('employee.user.name')
                    ->visible(fn() => Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])))
                    ->searchable(),
                TextColumn::make('leaveType.name')
                    ->searchable(),
                TextColumn::make('total_days')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->searchable(),
                TextColumn::make('end_date')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable()
                    ->badge()
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
                        ->visible(fn($record) => $user?->can('approve', $record) && $record->status === 'pending')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(
                            fn(LeaveRequest $record) =>
                            LeaveRequestActionStatus::approveAction($record)
                        ),
                    Action::make('reject')
                        ->label('Reject')
                        ->visible(fn($record) => $user?->can('approve', $record) && $record->status === 'pending')
                        ->icon('heroicon-o-x-circle')
                        ->color('error')
                        ->schema([
                            Textarea::make('note')
                                ->label('Reason Reject')
                                ->required()
                                ->rows(4)
                                ->maxLength(500),
                        ])
                        ->modalHeading('Reject Leave Request')
                        ->modalDescription('Masukkan alasan penolakan.')
                        ->modalSubmitActionLabel('Reject')
                        ->action(function ($record, array $data) {
                            $record->update([
                                'status' => 'rejected',
                                'rejected_by' => Auth::id(),
                                'rejected_at' => now(),
                                'notes' => $data['note'],
                            ]);
                        }),
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn($record) => $record->status === 'pending' || $user->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
