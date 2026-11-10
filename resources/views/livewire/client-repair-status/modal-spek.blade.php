<div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($repair->repairDevices as $device)
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                <h4 class="font-bold text-primary-600 dark:text-primary-400 mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">
                    Perangkat {{ $loop->iteration }}
                </h4>
                
                <div class="space-y-3">
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase">Merek & Model</span>
                        <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ $device->brand?->name }} {{ $device->model }}</span>
                    </div>
                    
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase">Serial Number</span>
                        <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ $device->serial_number ?: '-' }}</span>
                    </div>
                    
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase">Kelengkapan</span>
                        <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ $device->accessories ?: '-' }}</span>
                    </div>
                    
                    @if($device->checklist)
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase mb-1">Checklist Kondisi Fisik/Fungsi</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach(is_array($device->checklist) ? $device->checklist : [] as $key => $val)
                                    @if($val)
                                        <span class="px-2 py-0.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-xs">
                                            {{ is_string($key) ? $key : $val }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
