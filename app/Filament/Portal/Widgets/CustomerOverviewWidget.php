<?php

namespace App\Filament\Portal\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CustomerOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $user = Auth::user();
        
        // 1. Saldo Wallet
        $balance = $user->wallet ? $user->wallet->balance : 0;
        
        // 2. Langganan Aktif
        $activeSub = $user->getActiveSubscription();
        $planName = $activeSub ? ($activeSub->plan->name ?? 'Unknown') : 'Tidak ada langganan aktif';
        
        // 3. Tagihan Belum Dibayar
        $unpaidInvoices = Invoice::where('user_id', $user->id)
                                ->whereIn('status', ['unpaid', 'waiting_approval'])
                                ->count();

        return [
            Stat::make('Saldo Wallet', 'Rp ' . number_format($balance, 0, ',', '.'))
                ->description('Saldo Anda saat ini')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),
            
            Stat::make('Langganan Aktif', $planName)
                ->description($activeSub ? 'Berakhir pada: ' . $activeSub->ends_at->format('d M Y') : 'Mulai berlangganan sekarang')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($activeSub ? 'primary' : 'gray'),
                
            Stat::make('Menunggu Pembayaran', $unpaidInvoices)
                ->description($unpaidInvoices > 0 ? 'Ada tagihan yang perlu dibayar' : 'Semua tagihan lunas')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($unpaidInvoices > 0 ? 'danger' : 'success'),
        ];
    }
}
