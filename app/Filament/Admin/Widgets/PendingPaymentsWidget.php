<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PaymentConfirmation;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PaymentConfirmation::query()
                    ->where('status', 'pending')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('Invoice Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice.user.name')
                    ->label('User'),
                Tables\Columns\TextColumn::make('transfer_amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bankAccount.bank_name')
                    ->label('To Bank'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            // Optional: link it to the resource if you have one
            // ->recordUrl(
            //     fn(PaymentConfirmation $record): string => route('filament.admin.resources.payment-confirmations.view', ['record' => $record]),
            // )
            ->headerActions([
                Action::make('view_all')
                    ->label('Lihat Semua')
                    ->url(fn(): string => route('filament.admin.resources.payment-confirmations.index'))
                    ->icon('heroicon-m-arrow-right')
            ])
            ->paginated(false);
    }
}
