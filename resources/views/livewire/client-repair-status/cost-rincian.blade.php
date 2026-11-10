@php
    $invoice = $getRecord()->repairInvoice;
@endphp

<div class="py-2">
    @if(!$invoice || $invoice->grand_total == 0)
        <div class="text-center py-6 px-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
            <x-heroicon-o-document-currency-dollar class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Rincian biaya belum tersedia atau masih Rp 0</p>
        </div>
    @else
        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
            <div class="flex justify-between items-center mb-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Tagihan</span>
                <span class="text-lg font-extrabold text-primary-600 dark:text-primary-400">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span>
            </div>
            
            <div class="space-y-2 mb-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Telah Dibayar</span>
                    <span class="font-semibold text-success-600 dark:text-success-400">Rp {{ number_format($invoice->paid_amount ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Sisa Pembayaran</span>
                    <span class="font-semibold text-danger-600 dark:text-danger-400">Rp {{ number_format($invoice->remaining_amount ?? $invoice->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
            
            <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                    {{ $invoice->status === 'paid' ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400' : 
                       ($invoice->status === 'partially_paid' ? 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400' : 
                       'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400') }}">
                    @if($invoice->status === 'paid')
                        <x-heroicon-m-check-circle class="w-3.5 h-3.5" /> Lunas
                    @elseif($invoice->status === 'partially_paid')
                        <x-heroicon-m-clock class="w-3.5 h-3.5" /> Dibayar Sebagian
                    @else
                        <x-heroicon-m-x-circle class="w-3.5 h-3.5" /> Belum Dibayar
                    @endif
                </span>
            </div>
        </div>
    @endif
</div>
