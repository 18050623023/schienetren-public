<div class="relative pl-6 py-2">
    <!-- Vertical line -->
    <div class="absolute top-0 bottom-0 left-3 w-px bg-gray-200 dark:bg-gray-700"></div>
    
    @php
        // Dummy or basic timeline items based on Repair status.
        // In a real app, this should come from a history table or spatie/activitylog.
        
        $statuses = [
            ['label' => 'Servis Masuk (Penerimaan Unit)', 'date' => $getRecord()->created_at, 'icon' => 'heroicon-m-clipboard-document-check', 'color' => 'text-primary-500', 'bg' => 'bg-primary-50 dark:bg-primary-900/30'],
        ];
        
        if ($getRecord()->status === \App\Enums\RepairStatus::Delivered) {
            $statuses[] = ['label' => 'Unit Sudah Diambil (Selesai)', 'date' => $getRecord()->delivered_at ?? $getRecord()->updated_at, 'icon' => 'heroicon-m-check-badge', 'color' => 'text-success-500', 'bg' => 'bg-success-50 dark:bg-success-900/30'];
        } elseif ($getRecord()->status === \App\Enums\RepairStatus::Cancel) {
            $statuses[] = ['label' => 'Cancel', 'date' => $getRecord()->canceled_at ?? $getRecord()->updated_at, 'icon' => 'heroicon-m-x-circle', 'color' => 'text-danger-500', 'bg' => 'bg-danger-50 dark:bg-danger-900/30'];
        } else {
            $statuses[] = ['label' => $getRecord()->status->getLabel() ?? $getRecord()->status, 'date' => $getRecord()->updated_at, 'icon' => 'heroicon-m-arrow-path', 'color' => 'text-warning-500', 'bg' => 'bg-warning-50 dark:bg-warning-900/30'];
        }
        
        // Reverse so the newest is on top
        $statuses = array_reverse($statuses);
    @endphp

    <ul class="space-y-6">
        @foreach($statuses as $index => $item)
            <li class="relative">
                <!-- Dot/Icon -->
                <div class="absolute -left-6 mt-1 flex items-center justify-center w-6 h-6 rounded-full border-2 border-white dark:border-gray-800 {{ $index === 0 ? 'bg-success-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }} shadow-sm z-10">
                    @if($index === 0)
                        <x-heroicon-m-check class="w-3.5 h-3.5" />
                    @else
                        <div class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500"></div>
                    @endif
                </div>
                
                <div class="ml-2">
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mb-1">{{ \Carbon\Carbon::parse($item['date'])->format('d M Y H:i A') }}</p>
                    <div class="rounded-lg border border-gray-100 dark:border-gray-700 p-3 {{ $index === 0 ? $item['bg'] : 'bg-gray-50 dark:bg-gray-800/50' }}">
                        <p class="text-sm font-bold {{ $index === 0 ? $item['color'] : 'text-gray-700 dark:text-gray-300' }} flex items-center gap-1.5">
                            <x-dynamic-component :component="$item['icon']" class="w-4 h-4" />
                            {{ $item['label'] }}
                        </p>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>
