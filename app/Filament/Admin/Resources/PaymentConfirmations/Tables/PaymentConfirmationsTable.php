<?php

namespace App\Filament\Admin\Resources\PaymentConfirmations\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PaymentConfirmationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.invoice_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bankAccount.bank_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sender_bank_name')
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => $record->sender_bank_name . ' - ' . $record->sender_account_name ?? 'N/A'),
                TextColumn::make('transfer_amount')
                    ->label('Amount')
                    ->numeric()
                    ->sortable()
                    ->money('idr'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('reviewer.name')
                    ->sortable(),
                TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'pending')
                        ->form([
                            \Filament\Forms\Components\Textarea::make('notes')
                                ->label('Catatan')
                                ->helperText('Opsional'),
                        ])
                        ->action(function ($record, array $data) {
                            \App\Services\BillingService::approvePaymentConfirmation($record, Auth::id(), $data['notes']);
                            \Filament\Notifications\Notification::make()->title('Payment Approved')->success()->send();
                        }), 
                    Action::make('reject')
                        ->label('Reject')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->form([
                            \Filament\Forms\Components\Textarea::make('reason')
                                ->label('Alasan Penolakan')
                                ->required(),
                        ])
                        ->visible(fn($record) => $record->status === 'pending')
                        ->action(function ($record, array $data) {
                            \App\Services\BillingService::rejectPaymentConfirmation($record, Auth::id(), $data['reason']);
                            \Filament\Notifications\Notification::make()->title('Payment Rejected')->danger()->send();
                        }),
                    // EditAction::make(),
                    ViewAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', direction: 'desc');
    }
}
