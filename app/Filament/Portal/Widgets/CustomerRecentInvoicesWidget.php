<?php

namespace App\Filament\Portal\Widgets;

use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class CustomerRecentInvoicesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = null;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->where('user_id', Auth::id())
                    ->latest()
                    ->limit(5)
            )
            ->heading('Tagihan Terbaru')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No Invoice'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->getStateUsing(function (Invoice $record) {
                        $item = $record->items->first();
                        if (!$item) return '-';
                        if ($item->item_type === 'plan') {
                            return 'Langganan: ' . ($item->item->name ?? 'Plan');
                        } elseif ($item->item_type === 'addon') {
                            return 'Addon: ' . ($item->item->name ?? 'Addon');
                        } elseif ($item->item_type === 'topup') {
                            return 'Topup Saldo';
                        }
                        return ucwords(str_replace('_', ' ', $item->item_type));
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Jumlah')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'waiting_approval' => 'warning',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                        'expired' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime(),
            ])
            ->headerActions([
                Action::make('view_all')
                    ->label('Semua Tagihan')
                    ->url(fn () => \App\Filament\Portal\Pages\Billing::getUrl())
                    ->button()
            ])
            ->paginated(false);
    }
}
